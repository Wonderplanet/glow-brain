require_relative '../generator.rb'

class MstHeaderCsvGenerator < Generator
  def yaml_element_name
    return "data"
  end

  def file_name(name)
    return "#{name.underscore}.csv"
  end

  def translate(schema_yaml)
    return nil unless schema_yaml["name"] =~ /^Mst/
    return schema_yaml
  end
end
