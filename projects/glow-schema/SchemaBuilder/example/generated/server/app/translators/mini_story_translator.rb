class MiniStoryTranslator
  def self.translate(mini_story_model)
    view_model = MiniStoryViewModel.new
    view_model.opr_mini_story_id = mini_story_model.opr_mini_story_id
    view_model.play_count = mini_story_model.play_count
    view_model
  end
end
