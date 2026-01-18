using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.EventMission.Domain.Model
{
    public record EventMissionTimeUseCaseModel(
        RemainingTimeSpan RemainingEventTimeSpan,
        RemainingTimeSpan RemainingDailyBonusTimeSpan,
        RemainingTimeSpan RemainingDailyNextUpdateTimeSpan)
    {
        public static EventMissionTimeUseCaseModel Empty { get; } = new EventMissionTimeUseCaseModel(
            RemainingTimeSpan.Empty,
            RemainingTimeSpan.Empty,
            RemainingTimeSpan.Empty
        );
    };
}
