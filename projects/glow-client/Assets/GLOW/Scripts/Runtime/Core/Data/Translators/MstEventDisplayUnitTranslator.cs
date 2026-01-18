using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.Event;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class MstEventDisplayUnitTranslator
    {
        public static MstEventDisplayUnitModel Translate(
            MstEventDisplayUnitData data,
            MstEventDisplayUnitI18nData i18n)
        {
            return new MstEventDisplayUnitModel(
                new MasterDataId(data.Id),
                new MasterDataId(data.MstQuestId),
                new MasterDataId(data.MstUnitId),
                string.IsNullOrEmpty(i18n.SpeechBalloonText1)
                    ? EventDisplayUnitSpeechBalloonText.Empty
                    : new EventDisplayUnitSpeechBalloonText(i18n.SpeechBalloonText1),
                string.IsNullOrEmpty(i18n.SpeechBalloonText2)
                    ? EventDisplayUnitSpeechBalloonText.Empty
                    : new EventDisplayUnitSpeechBalloonText(i18n.SpeechBalloonText2),
                string.IsNullOrEmpty(i18n.SpeechBalloonText3)
                    ? EventDisplayUnitSpeechBalloonText.Empty
                    : new EventDisplayUnitSpeechBalloonText(i18n.SpeechBalloonText3)
            );
        }
    }
}
