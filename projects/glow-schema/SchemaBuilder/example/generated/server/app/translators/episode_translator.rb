class EpisodeTranslator
  def self.translate(episode_model)
    view_model = EpisodeViewModel.new
    view_model.id = episode_model.id
    view_model.mst_episode_id = episode_model.mst_episode_id
    view_model.play_count = episode_model.play_count
    view_model.clear_count = episode_model.clear_count
    view_model
  end
end
