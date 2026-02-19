require 'rails_helper'

RSpec.describe "EpisodeUseCase" do
  let(:use_case) { EpisodeUseCase.new }
  let(:user) { create(:user) }

  context "#create_session" do
    subject { use_case.create_session(user)  }

    it do
      # sample
      # data = subject
      # expect(data.is_a?(PuzzleSessionData)).to be true
      # expect(data.puzzle_session).to be_blank
      # expect(data.opponents).to be_blank
      # expect(data.session.is_a?(EpisodeSession)).to be true
      # expect(data.session).to be_present
    end
  end
  context "#delete_session" do
    subject { use_case.delete_session(user)  }

    it do
      # sample
      # data = subject
      # expect(data.is_a?(PuzzleSessionData)).to be true
      # expect(data.puzzle_session).to be_blank
      # expect(data.opponents).to be_blank
      # expect(data.session.is_a?(EpisodeSession)).to be true
      # expect(data.session).to be_present
    end
  end
end
