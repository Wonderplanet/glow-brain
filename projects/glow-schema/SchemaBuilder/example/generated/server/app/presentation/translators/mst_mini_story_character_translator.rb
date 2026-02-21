class MstMiniStoryCharacterTranslator
  def self.translate(mst_mini_story_character_model)
    view_model = MstMiniStoryCharacterViewModel.new
    view_model.mst_character_id = mst_mini_story_character_model.mst_character_id
    view_model.opr_mini_story_id = mst_mini_story_character_model.opr_mini_story_id
    view_model
  end
end
