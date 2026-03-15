using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.AttackModel;
using GLOW.Scenes.InGame.Domain.Models.AttackResultModel;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public interface IAttackProcess
    {
        AttackProcessResult UpdateAttacks(
            IReadOnlyList<IAttackModel> attacks,
            IReadOnlyList<CharacterUnitModel> characterUnits,
            IReadOnlyList<SpecialUnitModel> specialUnits,
            OutpostModel playerOutpost,
            OutpostModel enemyOutpost,
            DefenseTargetModel defenseTargetModel,
            BossAppearancePauseModel bossAppearancePause,
            StageTimeModel stageTime,
            RushModel rushModel,
            RushModel pvpOpponentRushModel,
            IReadOnlyList<PlacedItemModel> placedItems,
            IReadOnlyList<DeckUnitModel> playerDeckUnits,
            IReadOnlyList<DeckUnitModel> pvpOpponentDeckUnits,
            TickCount tickCount,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            MstPageModel mstPage);

        (IReadOnlyList<IAttackModel>, IReadOnlyList<IAttackResultModel>, IReadOnlyList<PlacedItemModel>) ExecuteAttacks(
            IReadOnlyList<IAttackTargetModel> attackTargetCandidates,
            IReadOnlyList<IAttackModel> attacks,
            BossAppearancePauseModel bossAppearancePause,
            IReadOnlyList<PlacedItemModel> placedItems,
            IReadOnlyList<DeckUnitModel> playerDeckUnits,
            IReadOnlyList<DeckUnitModel> pvpOpponentDeckUnits,
            TickCount tickCount,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            MstPageModel mstPage);

        AttackProcessApplyingUnitResult ApplyAttacks(
            IReadOnlyList<CharacterUnitModel> characterUnits, IReadOnlyList<HitAttackResultModel> attackResults);

        AttackProcessApplyingOutpostResult ApplyAttacks(
            OutpostModel outpost,
            IReadOnlyList<HitAttackResultModel> attackResults,
            IReadOnlyList<CharacterUnitModel> characterUnits);

        AttackProcessApplyingDefenseTargetResult ApplyAttacks(
            DefenseTargetModel defenseTarget,
            IReadOnlyList<HitAttackResultModel> attackResults);
    }
}
