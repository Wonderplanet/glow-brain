class MiniStoryReleaseResultTranslator
  def self.translate(mini_story_release_result_model)
    view_model = MiniStoryReleaseResultViewModel.new
    view_model.opr_mini_story_id = mini_story_release_result_model.opr_mini_story_id
    view_model.crystal = mini_story_release_result_model.crystal
    view_model
  end
end
