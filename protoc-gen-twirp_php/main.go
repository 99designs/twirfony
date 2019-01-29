package main

import (
	"io"
	"io/ioutil"
	"log"
	"os"

	"github.com/golang/protobuf/proto"
	plugin "github.com/golang/protobuf/protoc-gen-go/plugin"
)

func main() {
	req := readCodeGeneratorRequest(os.Stdin)

	if len(req.FileToGenerate) == 0 {
		log.Fatalln("no files to generate")
	}

	gen := &generator{}
	resp := gen.Generate(req)

	writeCodeGeneratorResponse(os.Stdout, resp)
}

func readCodeGeneratorRequest(in io.Reader) *plugin.CodeGeneratorRequest {

	data, err := ioutil.ReadAll(in)
	if err != nil {
		panic(err)
	}

	req := &plugin.CodeGeneratorRequest{}
	err = proto.Unmarshal(data, req)
	if err != nil {
		panic(err)
	}

	return req
}

func writeCodeGeneratorResponse(out io.Writer, resp *plugin.CodeGeneratorResponse) {

	data, err := proto.Marshal(resp)
	if err != nil {
		panic(err)
	}

	_, err = out.Write(data)
	if err != nil {
		panic(err)
	}
}
