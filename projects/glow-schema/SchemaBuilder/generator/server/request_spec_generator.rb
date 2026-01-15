require_relative '../generator.rb'

class RequestSpecGenerator < Generator
  def yaml_element_name
    return "api"
  end

  def file_name(name)
    return "#{name.tableize}_request_spec.rb"
  end

  def translate(schema_yaml)
    return schema_yaml
  end
end
