package main

import (
	"strings"
	"unicode"

	"github.com/golang/protobuf/protoc-gen-go/descriptor"
)

type serviceView struct {
	*descriptor.ServiceDescriptorProto
	file *descriptor.FileDescriptorProto
}

func (s *serviceView) Namespace() string {

	if s.file.Options.GetPhpNamespace() != "" {
		return s.file.Options.GetPhpNamespace()
	}

	parts := strings.Split(s.file.GetPackage(), ".")
	for i, part := range parts {
		parts[i] = upperFirst(part)
	}

	return strings.Join(parts, `\`)
}

func (s *serviceView) ClassName() string {
	return s.GetName() + "Client"
}

func (s *serviceView) InterfaceName() string {
	return s.GetName() + "Interface"
}

func (s *serviceView) StubName() string {
	return s.GetName() + "Stub"
}

func (s *serviceView) ExceptionName() string {
	return s.GetName() + "Exception"
}

func (s *serviceView) SourceFileName() string {
	return s.file.GetName()
}

func (s *serviceView) ServiceName() string {
	if s.file.GetPackage() == "" {
		return s.GetName()
	}

	return s.file.GetPackage() + "." + s.GetName()
}

func (s *serviceView) Methods() []*methodView {
	methods := make([]*methodView, len(s.GetMethod()))

	for i, proto := range s.GetMethod() {
		methods[i] = &methodView{proto}
	}

	return methods
}

type methodView struct {
	*descriptor.MethodDescriptorProto
}

func (m *methodView) RPCMethodName() string {
	return m.GetName()
}

func (m *methodView) PHPMethodName() string {
	return fixPHPKeywords(lowerFirst(m.GetName()))
}

func (m *methodView) PHPCallbackName() string {
	return "$on" + fixPHPKeywords(m.GetName())
}

func (m *methodView) InputType() string {
	name := m.GetInputType()
	parts := strings.Split(name, ".")

	return fixPHPKeywords(parts[len(parts)-1])
}

func (m *methodView) InputArg() string {
	name := m.GetInputType()
	parts := strings.Split(name, ".")

	return lowerFirst(parts[len(parts)-1])
}

func (m *methodView) OutputType() string {
	name := m.GetOutputType()
	parts := strings.Split(name, ".")

	return fixPHPKeywords(parts[len(parts)-1])
}

func upperFirst(input string) string {
	return firstTo(unicode.UpperCase, input)
}

func lowerFirst(input string) string {
	return firstTo(unicode.LowerCase, input)
}

func firstTo(toCase int, input string) string {
	if input == "" {
		return ""
	}
	rs := []rune(input)
	rs[0] = unicode.To(toCase, rs[0])
	return string(rs)
}

func fixPHPKeywords(in string) string {
	for _, w := range phpKeywords {
		if strings.ToLower(in) == w {
			return "PB" + in
		}
	}
	return in
}
