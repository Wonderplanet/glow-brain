using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Party;

namespace GLOW.Core.Domain.Translators
{
    public static class PartyBonusUnitTranslator
    {
        public static PartyBonusUnitModel Translate(MstEventBonusUnitModel model)
        {
            return new PartyBonusUnitModel(
                model.MstUnitId,
                model.BonusPercentage);
        }

        public static PartyBonusUnitModel Translate(MstQuestBonusUnitModel model)
        {
            return new PartyBonusUnitModel(
                model.MstUnitId,
                model.CoinBonusRate.ToEventBonusPercentage());
        }
    }
}
