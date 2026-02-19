class MstGroupStoryAppearCharacterTranslator
  def self.translate(mst_group_story_appear_character_model)
    view_model = MstGroupStoryAppearCharacterViewModel.new
    view_model.id = mst_group_story_appear_character_model.id
    view_model.mst_group_story_episode_id = mst_group_story_appear_character_model.mst_group_story_episode_id
    view_model.mst_character_id = mst_group_story_appear_character_model.mst_character_id
    view_model
  end
end
