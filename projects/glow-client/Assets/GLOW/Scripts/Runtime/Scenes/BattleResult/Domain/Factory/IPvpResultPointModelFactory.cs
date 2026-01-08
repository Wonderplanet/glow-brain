using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Scenes.PvpBattleResult.Domain.Model;

namespace GLOW.Scenes.BattleResult.Domain.Factory
{
    public interface IPvpResultPointModelFactory
    {
        PvpBattleResultPointModel CreatePvpResultPointModel(
            PvpPoint beforePvpPoint,
            PvpPoint afterPvpPoint,
            PvpEndResultBonusPointModel resultBonusPointModel);
    }
}