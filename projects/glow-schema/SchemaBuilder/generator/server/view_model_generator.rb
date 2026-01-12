require_relative '../generator.rb'

class ViewModelGenerator < Generator
  def yaml_element_name
    return "data"
  end

  def file_name(name)
    return "#{name.underscore}_view_model.rb"
  end

  def translate(schema_yaml)
    return schema_yaml
  end
end
