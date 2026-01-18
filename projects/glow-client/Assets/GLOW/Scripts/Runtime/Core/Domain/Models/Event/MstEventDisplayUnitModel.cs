using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models.Event
{
    public record MstEventDisplayUnitModel(
        MasterDataId Id,
        MasterDataId MstQuestId,
        MasterDataId MstUnitId,
        EventDisplayUnitSpeechBalloonText SpeechBalloonText1,
        EventDisplayUnitSpeechBalloonText SpeechBalloonText2,
        EventDisplayUnitSpeechBalloonText SpeechBalloonText3)
    {
        public static MstEventDisplayUnitModel Empty { get; } = new MstEventDisplayUnitModel(
            MasterDataId.Empty,
            MasterDataId.Empty,
            MasterDataId.Empty,
            EventDisplayUnitSpeechBalloonText.Empty,
            EventDisplayUnitSpeechBalloonText.Empty,
            EventDisplayUnitSpeechBalloonText.Empty
        );
        public bool IsEmpty() => ReferenceEquals(this, Empty);
    };
}
