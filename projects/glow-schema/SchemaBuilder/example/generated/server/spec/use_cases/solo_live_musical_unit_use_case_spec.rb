require 'rails_helper'

RSpec.describe "SoloLiveMusicalUnitUseCase" do
  let(:use_case) { SoloLiveMusicalUnitUseCase.new }
  let(:user) { create(:user) }

  context "#get_mine" do
    subject { use_case.get_mine(user)  }

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
  context "#edit" do
    subject { use_case.edit(user)  }

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
