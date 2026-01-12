require_relative '../generator.rb'

class RoutingGenerator < Generator
  def yaml_element_name
    return "api"
  end

  def file_name(name)
    return "#{name.tableize}.rb"
  end

  def translate(schema_yaml)
    return schema_yaml
  end
end
