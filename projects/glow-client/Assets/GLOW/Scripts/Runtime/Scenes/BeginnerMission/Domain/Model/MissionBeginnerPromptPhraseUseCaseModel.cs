using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Scenes.BeginnerMission.Domain.Model
{
    public record MissionBeginnerPromptPhraseUseCaseModel(BeginnerMissionPromptPhrase BeginnerMissionPromptPhrase)
    {
        public static MissionBeginnerPromptPhraseUseCaseModel Empty { get; } = new (
            BeginnerMissionPromptPhrase.Empty);
    }
}