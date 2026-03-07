require 'rails_helper'

RSpec.describe "CharacterVariantUseCase" do
  let(:use_case) { CharacterVariantUseCase.new }
  let(:user) { create(:user) }

  context "#enhance" do
    subject { use_case.enhance(user)  }

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
