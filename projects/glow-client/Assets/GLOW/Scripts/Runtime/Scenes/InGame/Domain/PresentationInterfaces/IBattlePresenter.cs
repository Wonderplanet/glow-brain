using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.AttackModel;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.PresentationInterfaces
{
    public interface IBattlePresenter
    {
        void OnSummonCharacterWithoutEffect(CharacterUnitModel characterUnitModel);
        void OnSummonCharacter(CharacterUnitModel characterUnitModel);
        void OnSummonSpecialUnit(SpecialUnitModel specialUnitModel, CoordinateRange coordinateRange);
        void OnGenerateGimmickObject(InGameGimmickObjectModel inGameGimmickObjectModel);
        void OnUpdateDeck(IReadOnlyList<DeckUnitModel> deckUnitModels, BattlePoint currentCP);
        void OnUpdateFieldObjects(OutpostModel playerOutpost,
            OutpostModel enemyOutpost,
            IReadOnlyList<CharacterUnitModel> characterUnitModels,
            IReadOnlyList<SpecialUnitModel> specialUnitModels,
            DefenseTargetModel defenseTarget,
            IReadOnlyList<AppliedAttackResultModel> appliedAttackResults);
        void OnAppearAttack(IAttackModel attackModel);
        void OnUpdateAttacks(IReadOnlyList<IAttackModel> attackModels);
        void OnEndAttack(IAttackModel attackModel);
        void OnUpdateBattlePoint(BattlePointModel battlePointModel);
        void OnExecuteRush(
            RushModel rushModel,
            AttackPower calculatedRushAttackPower,
            RushEvaluationType rushEvaluationType,
            BattleSide battleSide,
            IReadOnlyList<CharacterUnitModel> characterUnitModels,
            IReadOnlyList<DeckUnitModel> deckUnitModels);
        void OnUpdateRushGauge(RushModel rushModel);
        void OnUpdatePvpOpponentRushGauge(RushModel pvpOpponentRushModel);
        void OnEffectBlocked(FieldObjectId fieldObjectId);
        void OnPlaceItems(IReadOnlyList<PlacedItemModel> newPlacedItemModels);
        void OnRemoveConsumedItems(IReadOnlyList<PlacedItemModel> consumedItemModels);
        void OnRemovePlacedItems(IReadOnlyList<PlacedItemModel> placedItemModels);
        void OnSurvivedByGuts(FieldObjectId fieldObjectId);
        void OnDeckStateEffect(AppliedDeckStateEffectResultModel deckStateEffectResultModel);
        void OnGimmickObjectsRemoved(IReadOnlyList<InGameGimmickObjectModel> removedGimmickObjectModels);
        void OnGimmickObjectTransformationStarted(InGameGimmickObjectModel gimmickObjectModel);
        void OnUpdateKomas(IReadOnlyDictionary<KomaId, KomaModel> komaDictionary);
        void OnResetKomas(IReadOnlyDictionary<KomaId, KomaModel> komaDictionary);
        void OnBossAppearancePauseEnded();
        void OnMangaAnimationStart(IReadOnlyList<MangaAnimationModel> mangaAnimationModels);
        void OnUpdateTimeLimit(StageTimeModel stageTimeModel);
        void OnUpdateScore(
            InGameScore score,
            IReadOnlyList<ScoreCalculationResultModel> addedScoreModels,
            ScoreEffectVisibleFlag isScoreEffectVisible);
        void OnDefeatEnemy(
            DefeatEnemyCount defeatedCount,
            IReadOnlyDictionary<MasterDataId, DefeatEnemyCount> defeatEnemyCountDictionary);
        void OnSpeak(UnitSpeechBalloonModel speechBalloonModel);
        void OnVictory(StageEndConditionType battleEndConditionType);
        void OnDefeatWithContinue(
            StageEndConditionType battleEndConditionType);
        void OnDefeatCannotContinue(
            StageEndConditionType battleEndConditionType);
        void OnFinish(StageEndConditionType battleEndConditionType);
    }
}
