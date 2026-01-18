class SkillTreeNodeReleaseResultTranslator
  def self.translate(skill_tree_node_release_result_model)
    view_model = SkillTreeNodeReleaseResultViewModel.new
    view_model.skill_tree_node_release = skill_tree_node_release_result_model.skill_tree_node_release
    view_model.mst_skill_tree_node_ids = skill_tree_node_release_result_model.mst_skill_tree_node_ids
    view_model.after_character_variant = skill_tree_node_release_result_model.after_character_variant
    view_model.solo_story_episodes = skill_tree_node_release_result_model.solo_story_episodes
    view_model.character_variant_voice = skill_tree_node_release_result_model.character_variant_voice
    view_model.after_items = skill_tree_node_release_result_model.after_items
    view_model.updated_mission = skill_tree_node_release_result_model.updated_mission
    view_model
  end
end
