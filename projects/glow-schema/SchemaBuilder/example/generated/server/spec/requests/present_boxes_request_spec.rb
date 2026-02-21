require 'rails_helper'

RSpec.describe "GET /api/present_boxes", type: :request do
  subject { get "/api/present_boxes", params: {} }

  before do
    allow_any_instance_of(ApiController).to receive(:mock_user).and_return(double(:user))
    #stub sample allow_any_instance_of(EpisodeSessionUseCase).to receive(:create_session).and_return(double(:data))
    #stub sample allow(EpisodeSessionTranslator).to receive(:translate).and_return(double(:view_model))
    allow(Serializer).to receive(:exec).and_return("hoge")
  end

  it { expect(subject).to eq(200) }
end
RSpec.describe "POST /api/present_boxes/receive", type: :request do
  subject { post "/api/present_boxes/receive", params: {} }

  before do
    allow_any_instance_of(ApiController).to receive(:mock_user).and_return(double(:user))
    #stub sample allow_any_instance_of(EpisodeSessionUseCase).to receive(:create_session).and_return(double(:data))
    #stub sample allow(EpisodeSessionTranslator).to receive(:translate).and_return(double(:view_model))
    allow(Serializer).to receive(:exec).and_return("hoge")
  end

  it { expect(subject).to eq(200) }
end
