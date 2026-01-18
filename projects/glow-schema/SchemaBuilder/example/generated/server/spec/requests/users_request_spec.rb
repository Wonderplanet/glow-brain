require 'rails_helper'

RSpec.describe "GET /api/users/profiles", type: :request do
  subject { get "/api/users/profiles", params: {} }

  before do
    allow_any_instance_of(ApiController).to receive(:mock_user).and_return(double(:user))
    #stub sample allow_any_instance_of(EpisodeSessionUseCase).to receive(:create_session).and_return(double(:data))
    #stub sample allow(EpisodeSessionTranslator).to receive(:translate).and_return(double(:view_model))
    allow(Serializer).to receive(:exec).and_return("hoge")
  end

  it { expect(subject).to eq(200) }
end
RSpec.describe "GET /api/users/search", type: :request do
  subject { get "/api/users/search", params: {} }

  before do
    allow_any_instance_of(ApiController).to receive(:mock_user).and_return(double(:user))
    #stub sample allow_any_instance_of(EpisodeSessionUseCase).to receive(:create_session).and_return(double(:data))
    #stub sample allow(EpisodeSessionTranslator).to receive(:translate).and_return(double(:view_model))
    allow(Serializer).to receive(:exec).and_return("hoge")
  end

  it { expect(subject).to eq(200) }
end
RSpec.describe "PATCH /api/users/recover_ap", type: :request do
  subject { patch "/api/users/recover_ap", params: {} }

  before do
    allow_any_instance_of(ApiController).to receive(:mock_user).and_return(double(:user))
    #stub sample allow_any_instance_of(EpisodeSessionUseCase).to receive(:create_session).and_return(double(:data))
    #stub sample allow(EpisodeSessionTranslator).to receive(:translate).and_return(double(:view_model))
    allow(Serializer).to receive(:exec).and_return("hoge")
  end

  it { expect(subject).to eq(200) }
end
RSpec.describe "DELETE /api/users/delete", type: :request do
  subject { delete "/api/users/delete", params: {} }

  before do
    allow_any_instance_of(ApiController).to receive(:mock_user).and_return(double(:user))
    #stub sample allow_any_instance_of(EpisodeSessionUseCase).to receive(:create_session).and_return(double(:data))
    #stub sample allow(EpisodeSessionTranslator).to receive(:translate).and_return(double(:view_model))
    allow(Serializer).to receive(:exec).and_return("hoge")
  end

  it { expect(subject).to eq(200) }
end
