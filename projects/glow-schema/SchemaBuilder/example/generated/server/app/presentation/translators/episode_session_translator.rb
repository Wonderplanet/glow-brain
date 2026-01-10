class EpisodeSessionTranslator
  def self.translate(episode_session_model)
    view_model = EpisodeSessionViewModel.new
    view_model.category = episode_session_model.category
    view_model.episode_id = episode_session_model.episode_id
    view_model.mst_episode_id = episode_session_model.mst_episode_id
    view_model
  end
end
