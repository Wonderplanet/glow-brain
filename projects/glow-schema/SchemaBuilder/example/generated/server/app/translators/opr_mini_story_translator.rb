class OprMiniStoryTranslator
  def self.translate(opr_mini_story_model)
    view_model = OprMiniStoryViewModel.new
    view_model.id = opr_mini_story_model.id
    view_model.title = opr_mini_story_model.title
    view_model.asset_key = opr_mini_story_model.asset_key
    view_model.start_at = opr_mini_story_model.start_at
    view_model.end_at = opr_mini_story_model.end_at
    view_model.top_priority = opr_mini_story_model.top_priority
    view_model.release_at = opr_mini_story_model.release_at
    view_model
  end
end
