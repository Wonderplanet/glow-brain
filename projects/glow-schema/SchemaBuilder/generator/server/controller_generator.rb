require_relative '../generator.rb'

class ControllerGenerator < Generator
  def yaml_element_name
    return "api"
  end

  def file_name(name)
    return "#{name.tableize}_controller.rb"
  end

  def translate(schema_yaml)
    return schema_yaml
  end
end
