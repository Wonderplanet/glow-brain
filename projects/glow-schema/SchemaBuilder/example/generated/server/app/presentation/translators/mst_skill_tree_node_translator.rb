class MstSkillTreeNodeTranslator
  def self.translate(mst_skill_tree_node_model)
    view_model = MstSkillTreeNodeViewModel.new
    view_model.id = mst_skill_tree_node_model.id
    view_model.mst_character_variant_id = mst_skill_tree_node_model.mst_character_variant_id
    view_model.bit_number = mst_skill_tree_node_model.bit_number
    view_model.parent_bit_number = mst_skill_tree_node_model.parent_bit_number
    view_model.node_type = mst_skill_tree_node_model.node_type
    view_model.required_level = mst_skill_tree_node_model.required_level
    view_model.horizontal_position = mst_skill_tree_node_model.horizontal_position
    view_model.vertical_position = mst_skill_tree_node_model.vertical_position
    view_model.increase_hp_value = mst_skill_tree_node_model.increase_hp_value
    view_model.increase_performance_value = mst_skill_tree_node_model.increase_performance_value
    view_model.increase_heal_value = mst_skill_tree_node_model.increase_heal_value
    view_model.voice_key = mst_skill_tree_node_model.voice_key
    view_model
  end
end
