require 'rails_helper'

RSpec.describe "PATCH /api/gallery/home_bgms", type: :request do
  subject { patch "/api/gallery/home_bgms", params: {} }

  before do
    allow_any_instance_of(ApiController).to receive(:mock_user).and_return(double(:user))
    #stub sample allow_any_instance_of(EpisodeSessionUseCase).to receive(:create_session).and_return(double(:data))
    #stub sample allow(EpisodeSessionTranslator).to receive(:translate).and_return(double(:view_model))
    allow(Serializer).to receive(:exec).and_return("hoge")
  end

  it { expect(subject).to eq(200) }
end
RSpec.describe "PATCH /api/gallery/normal_song_bgms", type: :request do
  subject { patch "/api/gallery/normal_song_bgms", params: {} }

  before do
    allow_any_instance_of(ApiController).to receive(:mock_user).and_return(double(:user))
    #stub sample allow_any_instance_of(EpisodeSessionUseCase).to receive(:create_session).and_return(double(:data))
    #stub sample allow(EpisodeSessionTranslator).to receive(:translate).and_return(double(:view_model))
    allow(Serializer).to receive(:exec).and_return("hoge")
  end

  it { expect(subject).to eq(200) }
end
RSpec.describe "PATCH /api/gallery/last_song_bgms", type: :request do
  subject { patch "/api/gallery/last_song_bgms", params: {} }

  before do
    allow_any_instance_of(ApiController).to receive(:mock_user).and_return(double(:user))
    #stub sample allow_any_instance_of(EpisodeSessionUseCase).to receive(:create_session).and_return(double(:data))
    #stub sample allow(EpisodeSessionTranslator).to receive(:translate).and_return(double(:view_model))
    allow(Serializer).to receive(:exec).and_return("hoge")
  end

  it { expect(subject).to eq(200) }
end
