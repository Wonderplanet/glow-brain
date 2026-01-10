require 'rails_helper'

RSpec.describe "POST /api/normal_missions/receive_reward", type: :request do
  subject { post "/api/normal_missions/receive_reward", params: {} }

  before do
    allow_any_instance_of(ApiController).to receive(:mock_user).and_return(double(:user))
    #stub sample allow_any_instance_of(EpisodeSessionUseCase).to receive(:create_session).and_return(double(:data))
    #stub sample allow(EpisodeSessionTranslator).to receive(:translate).and_return(double(:view_model))
    allow(Serializer).to receive(:exec).and_return("hoge")
  end

  it { expect(subject).to eq(200) }
end
