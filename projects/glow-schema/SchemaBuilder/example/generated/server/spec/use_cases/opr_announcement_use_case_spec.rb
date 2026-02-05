require 'rails_helper'

RSpec.describe "OprAnnouncementUseCase" do
  let(:use_case) { OprAnnouncementUseCase.new }
  let(:user) { create(:user) }

  context "#get" do
    subject { use_case.get(user)  }

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
