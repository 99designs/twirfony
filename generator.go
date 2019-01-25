package main

import (
	"bytes"
	"fmt"
	"strings"
	"text/template"

	"github.com/golang/protobuf/proto"
	"github.com/golang/protobuf/protoc-gen-go/descriptor"
	plugin "github.com/golang/protobuf/protoc-gen-go/plugin"
)

type generator struct{}

func (g *generator) Generate(req *plugin.CodeGeneratorRequest) *plugin.CodeGeneratorResponse {
	resp := &plugin.CodeGeneratorResponse{}

	for _, name := range req.FileToGenerate {
		file := getFileDescriptor(req, name)

		for _, service := range file.Service {
			presenter := &serviceView{service, file}
			resp.File = append(resp.File, generateFile(presenter, interfaceTemplate, presenter.InterfaceName()))
			resp.File = append(resp.File, generateFile(presenter, exceptionTemplate, presenter.ExceptionName()))
			resp.File = append(resp.File, generateFile(presenter, clientTemplate, presenter.ClassName()))
			resp.File = append(resp.File, generateFile(presenter, stubTemplate, presenter.StubName()))
		}
	}
	return resp
}

func generateFile(presenter *serviceView, t *template.Template, name string) *plugin.CodeGeneratorResponse_File {

	buffer := &bytes.Buffer{}
	err := t.Execute(buffer, presenter)
	if err != nil {
		panic(err)
	}

	resp := &plugin.CodeGeneratorResponse_File{}
	resp.Name = proto.String(getFileName(presenter.Namespace(), name))
	resp.Content = proto.String(buffer.String())
	return resp
}

func getFileDescriptor(req *plugin.CodeGeneratorRequest, name string) *descriptor.FileDescriptorProto {
	for _, d := range req.ProtoFile {
		if d.GetName() == name {
			return d
		}
	}

	panic(fmt.Errorf("could not find descriptor for %q", name))
}

func getFileName(ns, class string) string {
	return strings.Join(strings.Split(ns, `\`), "/") + "/" + class + ".php"
}
