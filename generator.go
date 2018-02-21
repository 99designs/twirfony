package main

import (
	"bytes"
	"fmt"
	"strings"

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

			resp.File = append(resp.File, g.generateInterface(presenter))
			resp.File = append(resp.File, g.generateException(presenter))
			resp.File = append(resp.File, g.generateClient(presenter))
		}
	}
	return resp
}

func (g *generator) generateInterface(presenter *serviceView) *plugin.CodeGeneratorResponse_File {

	buffer := &bytes.Buffer{}
	err := interfaceTemplate.Execute(buffer, presenter)
	if err != nil {
		panic(err)
	}

	resp := &plugin.CodeGeneratorResponse_File{}
	resp.Name = proto.String(getFileName(presenter.Namespace(), presenter.InterfaceName()))
	resp.Content = proto.String(buffer.String())
	return resp
}

func (g *generator) generateClient(presenter *serviceView) *plugin.CodeGeneratorResponse_File {

	buffer := &bytes.Buffer{}
	err := clientTemplate.Execute(buffer, presenter)
	if err != nil {
		panic(err)
	}

	resp := &plugin.CodeGeneratorResponse_File{}
	resp.Name = proto.String(getFileName(presenter.Namespace(), presenter.ClassName()))
	resp.Content = proto.String(buffer.String())
	return resp
}

func (g *generator) generateException(presenter *serviceView) *plugin.CodeGeneratorResponse_File {

	buffer := &bytes.Buffer{}
	err := exceptionTemplate.Execute(buffer, presenter)
	if err != nil {
		panic(err)
	}

	resp := &plugin.CodeGeneratorResponse_File{}
	resp.Name = proto.String(getFileName(presenter.Namespace(), presenter.ExceptionName()))
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
