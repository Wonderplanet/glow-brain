class ServerTimeTranslator
  def self.translate(server_time_model)
    view_model = ServerTimeViewModel.new
    view_model.date_time_offset = server_time_model.date_time_offset
    view_model
  end
end
