require 'rails_helper'

RSpec.describe "POST /api/event_normal_quests/:id/session", type: :request do
  subject { post "/api/event_normal_quests/:id/session", params: {} }

  before do
    allow_any_instance_of(ApiController).to receive(:mock_user).and_return(double(:user))
    #stub sample allow_any_instance_of(EpisodeSessionUseCase).to receive(:create_session).and_return(double(:data))
    #stub sample allow(EpisodeSessionTranslator).to receive(:translate).and_return(double(:view_model))
    allow(Serializer).to receive(:exec).and_return("hoge")
  end

  it { expect(subject).to eq(200) }
end
RSpec.describe "GET /api/event_normal_quests/current_session", type: :request do
  subject { get "/api/event_normal_quests/current_session", params: {} }

  before do
    allow_any_instance_of(ApiController).to receive(:mock_user).and_return(double(:user))
    #stub sample allow_any_instance_of(EpisodeSessionUseCase).to receive(:create_session).and_return(double(:data))
    #stub sample allow(EpisodeSessionTranslator).to receive(:translate).and_return(double(:view_model))
    allow(Serializer).to receive(:exec).and_return("hoge")
  end

  it { expect(subject).to eq(200) }
end
RSpec.describe "POST /api/event_normal_quests/session/puzzle_result", type: :request do
  subject { post "/api/event_normal_quests/session/puzzle_result", params: {} }

  before do
    allow_any_instance_of(ApiController).to receive(:mock_user).and_return(double(:user))
    #stub sample allow_any_instance_of(EpisodeSessionUseCase).to receive(:create_session).and_return(double(:data))
    #stub sample allow(EpisodeSessionTranslator).to receive(:translate).and_return(double(:view_model))
    allow(Serializer).to receive(:exec).and_return("hoge")
  end

  it { expect(subject).to eq(200) }
end
RSpec.describe "GET /api/event_normal_quests/session/puzzle_result", type: :request do
  subject { get "/api/event_normal_quests/session/puzzle_result", params: {} }

  before do
    allow_any_instance_of(ApiController).to receive(:mock_user).and_return(double(:user))
    #stub sample allow_any_instance_of(EpisodeSessionUseCase).to receive(:create_session).and_return(double(:data))
    #stub sample allow(EpisodeSessionTranslator).to receive(:translate).and_return(double(:view_model))
    allow(Serializer).to receive(:exec).and_return("hoge")
  end

  it { expect(subject).to eq(200) }
end
RSpec.describe "DELETE /api/event_normal_quests/session", type: :request do
  subject { delete "/api/event_normal_quests/session", params: {} }

  before do
    allow_any_instance_of(ApiController).to receive(:mock_user).and_return(double(:user))
    #stub sample allow_any_instance_of(EpisodeSessionUseCase).to receive(:create_session).and_return(double(:data))
    #stub sample allow(EpisodeSessionTranslator).to receive(:translate).and_return(double(:view_model))
    allow(Serializer).to receive(:exec).and_return("hoge")
  end

  it { expect(subject).to eq(200) }
end
