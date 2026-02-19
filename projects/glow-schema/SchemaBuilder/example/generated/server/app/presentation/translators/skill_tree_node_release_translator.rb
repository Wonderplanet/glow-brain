class SkillTreeNodeReleaseTranslator
  def self.translate(skill_tree_node_release_model)
    view_model = SkillTreeNodeReleaseViewModel.new
    view_model.mst_character_variant_id = skill_tree_node_release_model.mst_character_variant_id
    view_model.last_released_bit_number = skill_tree_node_release_model.last_released_bit_number
    view_model.flags = skill_tree_node_release_model.flags
    view_model
  end
end
