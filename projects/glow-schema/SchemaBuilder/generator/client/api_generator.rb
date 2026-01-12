require_relative '../generator.rb'

class ApiGenerator < Generator
  def yaml_element_name
    return "api"
  end

  def file_name(name, elements)
    return "#{name}Api.cs"
  end

  def translate(schema_yaml, base_schema_yaml)
    view_model = {} 
    view_model["name"] = schema_yaml["name"]
    view_model["actions"] = [] 

    schema_yaml["actions"].each do |action|
      action_view_model = {}
      action_view_model["name"] = action["name"]
      action_view_model["params"] = [] 
      action_view_model["resource_ids"] = [] 
      action_view_model["method"] = action["method"]
      action_view_model["response"] = action["response"]
      action_view_model["path"] = action["path"]
      action_view_model["all_params"] = [] 

      resource_ids = action["path"].scan(/{:(.+?)}/)

      resource_ids.each do |resource_id|
        id = resource_id[0]
        item = {"name" => id,  "type" => "int"}
        action_view_model["resource_ids"] << item 
        action_view_model["all_params"] << item 
      end 

      unless action["params"].nil?
        action["params"].each do |parameter|
          action_view_model["params"] << parameter
          action_view_model["all_params"] << parameter 
        end
      end
      if action["getProgress"]
        action_view_model["progress"] = true
      end

      if action["async"]
        action_view_model["async"] = true
      end

      action_view_model["placeholder_path"] = action["path"]
      action_view_model["resource_ids"].each_with_index do |item, index|
        id = item["name"]
        action_view_model["placeholder_path"].sub!(/{:#{id}}/, "{#{index}}")
      end

      view_model["actions"] << action_view_model
    end

    return view_model
  end
end
