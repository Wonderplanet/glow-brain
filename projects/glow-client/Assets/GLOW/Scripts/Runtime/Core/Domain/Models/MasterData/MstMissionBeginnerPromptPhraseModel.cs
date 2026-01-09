using System;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Mission;

namespace GLOW.Core.Domain.Models
{
    public record MstMissionBeginnerPromptPhraseModel(
        MasterDataId Id,
        BeginnerMissionPromptPhrase BeginnerMissionPromptPhrase,
        DateTimeOffset StartDate,
        DateTimeOffset EndDate)
    {
        public static MstMissionBeginnerPromptPhraseModel Empty { get; } = new(
            MasterDataId.Empty,
            BeginnerMissionPromptPhrase.Empty,
            DateTimeOffset.MinValue,
            DateTimeOffset.MaxValue);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}