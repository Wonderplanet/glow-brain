class MstEventStoryEpisodeTranslator
  def self.translate(mst_event_story_episode_model)
    view_model = MstEventStoryEpisodeViewModel.new
    view_model.id = mst_event_story_episode_model.id
    view_model.episode_number = mst_event_story_episode_model.episode_number
    view_model.name = mst_event_story_episode_model.name
    view_model.opr_event_id = mst_event_story_episode_model.opr_event_id
    view_model.consume_item_amount = mst_event_story_episode_model.consume_item_amount
    view_model.consume_credit_amount = mst_event_story_episode_model.consume_credit_amount
    view_model.is_movie = mst_event_story_episode_model.is_movie
    view_model.release_at = mst_event_story_episode_model.release_at
    view_model
  end
end
