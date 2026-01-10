using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Modules.MultipleSwitchController;
using GLOW.Core.Presentation.Modules.Audio;
using GLOW.Modules.CommonToast.Presentation;
using GLOW.Modules.GameOption.Domain.ValueObjects;
using GLOW.Modules.Tutorial.Presentation.Manager;
using GLOW.Modules.Tutorial.Presentation.Views;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.AttackModel;
using GLOW.Scenes.InGame.Domain.ScriptableObjects;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.InGame.Presentation.Field;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using GLOW.Scenes.InGame.Presentation.ViewModels;
using UIKit;
using UnityEngine;
using WPFramework.Presentation.Extensions;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Scenes.InGame.Presentation.Views
{
    public class InGameViewController : UIViewController<InGameView>, IEscapeResponder
    {
        [Inject] IInGameViewDelegate ViewDelegate { get; }
        [Inject] IEscapeResponderRegistry EscapeResponderRegistry { get; }
        [Inject] ISystemSoundEffectProvider SystemSoundEffectProvider { get; }
        [Inject] IInGameTutorialBackKeyViewDelegate InGameTutorialBackKeyHandler { get; }

        public bool IsInitialized { set; get; }

        public bool IsDeadAnimation
        {
            get => ActualView.IsDeadAnimation;
            set => ActualView.IsDeadAnimation = value;
        }

        public override void ViewDidLoad()
        {
            base.ViewDidLoad();

            ViewDelegate.OnViewDidLoad();
            EscapeResponderRegistry.Bind(this,ActualView);
        }

        public override void ViewDidUnload()
        {
            base.ViewDidUnload();

            ViewDelegate.OnViewDidUnload();
        }

        public async UniTask Initialize(InitializeViewModel initializeViewModel, CancellationToken cancellationToken)
        {
            await ActualView.InitializeInGame(initializeViewModel, cancellationToken);

            ActualView.OnSummonButtonTapped = characterId => ViewDelegate.OnCharacterSummonButtonTapped(characterId);
            ActualView.OnUseSpecialAttackButtonTapped = characterId => ViewDelegate.OnUseSpecialAttackButtonTapped(characterId);
            ActualView.OnSpecialUnitSummonButtonTapped = characterId => ViewDelegate.OnSpecialUnitSummonButtonTapped(characterId);
            ActualView.OnUnitDetailLongPressed = userUnitId => ViewDelegate.OnUnitDetailLongPress(userUnitId);
        }

        public void OnSummonCharacterWithoutEffect(CharacterUnitModel characterUnitModel)
        {
            ActualView.OnSummonCharacterWithoutEffect(characterUnitModel);
        }

        public void OnSummonCharacter(CharacterUnitModel characterUnitModel, Action onSummonComplete)
        {
            ActualView.OnSummonCharacter(characterUnitModel, onSummonComplete);
        }

        public async UniTask OnSummonSpecialUnit(
            SpecialUnitModel specialUnitModel,
            Action onStartSpecialAttack,
            Action onEndSpecialAttack,
            CancellationToken cancellationToken)
        {
            await ActualView.OnSummonSpecialUnit(
                specialUnitModel,
                onStartSpecialAttack,
                onEndSpecialAttack,
                cancellationToken);
        }

        public void OnGenerateGimmickObject(InGameGimmickObjectModel inGameGimmickObjectModel)
        {
            ActualView.OnGenerateGimmickObject(inGameGimmickObjectModel);
        }

        public void OnAppearAttack(IAttackModel attackModel)
        {
            ActualView.OnAppearAttack(attackModel);
        }

        public void OnUpdateAttacks(IReadOnlyList<IAttackModel> attackModels)
        {
            ActualView.OnUpdateAttacks(attackModels);
        }

        public void OnEndAttack(IAttackModel attackModel)
        {
            ActualView.OnEndAttack(attackModel);
        }

        public void OnUpdateDeck(IReadOnlyList<DeckUnitViewModel> deckUnitViewModels)
        {
            ActualView.OnUpdateDeck(deckUnitViewModels);
        }

        public void OnUpdateFieldObjects(
            OutpostModel playerOutpost,
            OutpostModel enemyOutpost,
            IReadOnlyList<CharacterUnitModel> characterUnitModels,
            IReadOnlyList<SpecialUnitModel> specialUnitModels,
            DefenseTargetModel defenseTarget,
            IReadOnlyList<AppliedAttackResultModel> appliedAttackResults,
            DamageDisplayFlag isDamageDisplay)
        {
            ActualView.OnUpdateFieldObjects(
                playerOutpost,
                enemyOutpost,
                characterUnitModels,
                specialUnitModels,
                defenseTarget,
                appliedAttackResults,
                isDamageDisplay);
        }

        public void OnUpdatedBattlePoint(BattlePointModel battlePointModel)
        {
            ActualView.OnUpdatedBattlePoint(battlePointModel);
        }

        public void OnUpdateRush(RushModel rushModel)
        {
            ActualView.OnUpdateRush(rushModel);
        }

        public void OnUpdateOpponentRush(RushModel pvpOpponentRushModel)
        {
            ActualView.OnUpdateOpponentRush(pvpOpponentRushModel);
        }

        public void OnEffectBlocked(FieldObjectId fieldObjectId)
        {
            ActualView.OnEffectBlocked(fieldObjectId);
        }
        
        public void OnPlaceItems(IReadOnlyList<PlacedItemModel> newPlacedItemModels)
        {
            foreach (var placedItemModel in newPlacedItemModels)
            {
                ActualView.OnPlaceItem(placedItemModel);
            }
        }
        
        public void OnRemoveConsumedItems(IReadOnlyList<PlacedItemModel> consumedItemModels)
        {
            foreach (var consumedItemModel in consumedItemModels)
            {
                ActualView.OnRemoveConsumedItem(consumedItemModel);
            }
        }
        
        public void OnRemovePlacedItems(IReadOnlyList<PlacedItemModel> placedItemModels)
        {
            foreach (var placedItemModel in placedItemModels)
            {
                ActualView.OnRemovePlacedItem(placedItemModel);
            }
        }

        public void OnSurvivedByGuts(FieldObjectId fieldObjectId)
        {
            ActualView.OnSurvivedByGuts(fieldObjectId);
        }

        public void OnRushAttackPowerUp(
            BattleSide targetDeckBattleSide,
            IReadOnlyList<FieldObjectId> attackerIds,
            PercentageM updatedPowerUp)
        {
            foreach (var attackerId in attackerIds)
            {
                ActualView.OnRushAttackPowerUp(targetDeckBattleSide, attackerId, updatedPowerUp);
            }
        }

        public void OnCoolTimeVariation(
            BattleSide targetDeckBattleSide,
            StateEffectType stateEffectType,
            MasterDataId targetCharacterId)
        {
            ActualView.OnCoolTimeVariation(
                targetDeckBattleSide,
                stateEffectType,
                targetCharacterId);
        }

        public void OnGimmickObjectsRemoved(IReadOnlyList<InGameGimmickObjectModel> removedGimmickObjectModels)
        {
            ActualView.OnGimmickObjectsRemoved(removedGimmickObjectModels);
        }

        public void OnGimmickObjectTransformationStarted(InGameGimmickObjectModel gimmickObjectModel)
        {
            ActualView.OnGimmickObjectTransformationStarted(gimmickObjectModel.AutoPlayerSequenceElementId);
        }

         public void SetUpKomas(IReadOnlyDictionary<KomaId, KomaModel> komaDictionary)
         {
             ActualView.SetUpKomas(komaDictionary);
         }

        public void OnUpdateKomas(IReadOnlyDictionary<KomaId, KomaModel> komaDictionary)
        {
            ActualView.OnUpdateKomas(komaDictionary);
        }

        public void OnResetKomas(IReadOnlyDictionary<KomaId, KomaModel> komaDictionary)
        {
            ActualView.OnResetKomas(komaDictionary);
        }

        public void OnSpeak(UnitSpeechBalloonModel speechBalloonModel)
        {
            ActualView.OnSpeak(speechBalloonModel);
        }

        public void OnUpdateTimeLimit(StageTimeModel stageTimeModel)
        {
            ActualView.OnUpdateTimeLimit(stageTimeModel);
        }

        public void OnTimeCountDown(TimeCountDown.EnumTimeCountDownType timeCountDownType)
        {
            // 残り時間表示
            ActualView.OnTimeCountDown(timeCountDownType);
            SoundEffectPlayer.Play(SoundEffectId.SSE_051_055);
        }

        public void OnUpdateScore(
            InGameScore score,
            IReadOnlyList<ScoreCalculationResultModel> addedScoreModels,
            ScoreEffectVisibleFlag isScoreEffectVisible)
        {
            ActualView.OnUpdateScore(score, addedScoreModels, isScoreEffectVisible);
        }

        public void OnDefeatEnemy(
            StageEndConditionType stageEndConditionType,
            DefeatEnemyCount defeatedCount,
            DefeatEnemyCount endCondition)
        {
            ActualView.OnDefeatEnemy(stageEndConditionType, defeatedCount, endCondition);
        }

        public void OnVictory()
        {
            ActualView.OnVictory();
        }

        public void OnDefeat()
        {
            ActualView.OnDefeat();
        }

        public void OnFinish()
        {
            ActualView.OnFinish();
        }

        public void PlayerOutpostBroken()
        {
            ActualView.PlayerOutpostBroken();
        }

        public void EnemyOutpostBroken()
        {
            ActualView.EnemyOutpostBroken();
        }

        public void RecoverPlayerOutpost()
        {
            ActualView.RecoverPlayerOutpost();
        }

        public void ShowDefeatUI()
        {
            ActualView.ShowDefeatUI();
        }

        public void SetBattleSpeed(BattleSpeed battleSpeed)
        {
            ActualView.SetBattleSpeed(battleSpeed);
        }

        public void OnAutoSwitched(InGameAutoEnabledFlag isAutoEnabled)
        {
            ActualView.SwitchAutoButton(isAutoEnabled);
        }

        public void SetButtonsToFrontViewCanvas()
        {
            ActualView.SetButtonsToFrontViewCanvas();
        }

        public void ResetMovedUI()
        {
            ActualView.ResetMovedUI();
        }

        public void StartSpecialUnitSummonTargetSelection(
            MasterDataId characterId,
            SpecialUnitSummonKomaRange komaRange,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            IReadOnlyList<KomaId> summoningKomaIds)
        {
            ActualView.StartSpecialUnitSummonTargetSelection(
                (pos) => ViewDelegate.SelectSpecialUnitSummonTarget(characterId, pos),
                komaRange,
                komaDictionary,
                summoningKomaIds);
        }

        public void EndKomaSelection()
        {
            ActualView.EndKomaSelection();
        }

        public void StartSpecialUnitSkillConfirmationWindow()
        {
            ActualView.StartSpecialUnitSkillConfirmationWindow();
        }

        public void EndSpecialUnitSkillConfirmationWindow()
        {
            ActualView.EndSpecialUnitSkillConfirmationWindow();
        }

        /// <summary> スペシャルユニット選択中用の選択可能コマ更新 </summary>
        public void UpdateKomaSelectable(
            SpecialUnitSummonKomaRange komaRange,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            IReadOnlyList<KomaId> specialUnitKomaIds)
        {
            ActualView.UpdateKomaSelectable(komaRange, komaDictionary, specialUnitKomaIds);
        }

        /// <summary> スペシャルユニットの必殺技発動中に表示される、効果範囲内コマの強調表示 </summary>
        public void ShowHighlightKomaWithinSpecialCoordinateRange(CoordinateRange fieldViewCoordinateRange)
        {
            ActualView.ShowHighlightKomaWithinSpecialCoordinateRange(fieldViewCoordinateRange);
        }

        /// <summary> スペシャルユニットの必殺技発動中の強調表示から通常に戻す </summary>
        public void HideKomaHighlightWithinSpecialCoordinateRange()
        {
            ActualView.HideKomaHighlightWithinSpecialCoordinateRange();
        }

        public async UniTask ShowBlackCurtain(CancellationToken cancellationToken)
        {
            await ActualView.ShowBlackCurtain(cancellationToken);
        }

        public async UniTask ShowBlackCurtain(float alpha, CancellationToken cancellationToken)
        {
            await ActualView.ShowBlackCurtain(alpha, cancellationToken);
        }

        public async UniTask HideBlackCurtain(CancellationToken cancellationToken)
        {
            await ActualView.HideBlackCurtain(cancellationToken);
        }

        public async UniTask ShowIndividualBlackCurtain(CancellationToken cancellationToken)
        {
            await ActualView.ShowIndividualBlackCurtain(cancellationToken);
        }

        public async UniTask HideIndividualBlackCurtain(CancellationToken cancellationToken)
        {
            await ActualView.HideIndividualBlackCurtain(cancellationToken);
        }

        public void ShowBattleFieldBlackCurtain()
        {
            ActualView.ShowBattleFieldBlackCurtain();
        }

        public void HideBattleFieldBlackCurtain()
        {
            ActualView.HideBattleFieldBlackCurtain();
        }

        public void EnableTapGuard()
        {
            ActualView.EnableTapGuard();
        }

        public void DisableTapGuard()
        {
            if (ActualView != null)
            {
                ActualView.DisableTapGuard();
            }
        }

        public void EnablePageTapGuard()
        {
            ActualView.EnablePageTapGuard();
        }

        public void DisablePageTapGuard()
        {
            ActualView.DisablePageTapGuard();
        }

        public void EnableHeaderTapGuard()
        {
            ActualView.EnableHeaderTapGuard();
        }

        public void DisableHeaderTapGuard()
        {
            ActualView.DisableHeaderTapGuard();
        }

        public void EnableFooterTapGuard()
        {
            ActualView.EnableFooterTapGuard();
        }

        public void DisableFooterTapGuard()
        {
            ActualView.DisableFooterTapGuard();
        }

        public void DisableFadeFooterTabGuard()
        {
            ActualView.DisableFadeFooterTapGuard();
        }

        public void ResetTouchCallBack()
        {
            ActualView.ResetTouchCallBack();
        }

        public void ShowTapToSkip(float delay = 0f)
        {
            ActualView.ShowTapToSkip(delay);
        }

        public void HideTapToSkip()
        {
            if (ActualView != null)
            {
                ActualView.HideTapToSkip();
            }
        }

        public void ShowTapToStartBattle()
        {
            ActualView.ShowTapToStartBattle();
        }

        public void HideTapToStartBattle()
        {
            ActualView.HideTapToStartBattle();
        }

        public void SetDefenseTargetHighlight(bool isHighlight)
        {
            ActualView.SetDefenseTargetHighlight(isHighlight);
        }

        public void SetPlayerOutpostHpHighlight(bool isHighlight)
        {
            ActualView.SetPlayerOutpostHpHighlight(isHighlight);
        }

        public void ChangeDeckMode()
        {
            ActualView.ChangeDeckMode();
        }
        
        public void SetDamageDisplay(DamageDisplayFlag isDamageDisplay)
        {
            ActualView.SetDamageDisplay(isDamageDisplay);
        }

        public PageCoordV2 GetDisplayingPageCenter()
        {
            return ActualView.GetDisplayingPageCenter();
        }

        public async UniTask ScrollPage(
            FieldViewCoordV2 targetPos,
            float scrollMaxDuration,
            CancellationToken cancellationToken,
            bool isIndependentUpdate = false,
            bool isUnrestricted = false)
        {
            await ActualView.ScrollPage(targetPos, scrollMaxDuration, cancellationToken, isIndependentUpdate, isUnrestricted);
        }

        public async UniTask ScrollPage(
            float targetNormalizedPosY,
            float duration,
            CancellationToken cancellationToken,
            bool isIndependentUpdate = false)
        {
            await ActualView.ScrollPage(targetNormalizedPosY, duration, cancellationToken, isIndependentUpdate);
        }

        public async UniTask ScalePage(float scale, float duration, CancellationToken cancellationToken)
        {
            await ActualView.ScalePage(scale, duration, cancellationToken);
        }

        public async UniTask ScalePage(
            FieldViewCoordV2 targetPos,
            float scale,
            float duration,
            CancellationToken cancellationToken)
        {
            await ActualView.ScalePage(targetPos, scale, duration, cancellationToken);
        }

        public void ScalePage(FieldViewCoordV2 targetPos, float scale)
        {
            ActualView.ScalePage(targetPos, scale);
        }

        public void ResetPageScale()
        {
            if (ActualView != null)
            {
                ActualView.ResetPageScale();
            }
        }

        public void StartUnitTracking(FieldObjectId id)
        {
            ActualView.StartUnitTracking(id);
        }

        public void EndUnitTracking()
        {
            ActualView.EndUnitTracking();
        }

        public MultipleSwitchHandler ExpandKomaSetFieldImage(FieldViewCoordV2 targetPos)
        {
            return ActualView.ExpandKomaSetFieldImage(targetPos);
        }

        public MultipleSwitchHandler PauseBattleField()
        {
            return ActualView.PauseBattleField();
        }

        public MultipleSwitchHandler PauseWithoutPlayerUnit()
        {
            return ActualView.PauseWithoutPlayerUnit();
        }

        public MultipleSwitchHandler PauseWithout(FieldObjectId id)
        {
            return ActualView.PauseWithout(id);
        }

        public MultipleSwitchHandler PauseWithoutDarknessClear()
        {
            return ActualView.PauseWithoutDarknessClear();
        }

        public MultipleSwitchHandler PauseWithoutDarknessClearAndPlayerUnit()
        {
            return ActualView.PauseWithoutDarknessClearAndPlayerUnit();
        }

        public async UniTask PlayMangaAnimation(
            MangaAnimationAssetKey assetKey,
            MangaAnimationSpeed animationSpeed,
            CancellationToken cancellationToken)
        {
            await ActualView.PlayMangaAnimation(assetKey, animationSpeed, cancellationToken);
        }

        public async UniTask PlayCutIn(
            CharacterColor unitColor,
            UnitAssetKey unitAssetKey,
            UnitAttackViewInfo attackViewInfo,
            CancellationToken cancellationToken)
        {
            await ActualView.PlayCutIn(unitColor, unitAssetKey, attackViewInfo, cancellationToken);
        }

        public async UniTask PlayRush(
            IReadOnlyList<UnitAssetKey> unitAssetKeys,
            IReadOnlyList<UnitAssetKey> specialUnitAssetKey,
            PercentageM specialUnitBonus,
            Action unpauseAction,
            BattleSide battleSide,
            RushChargeCount chargeCount,
            AttackPower calculatedRushAttackPower,
            RushEvaluationType rushEvaluationType,
            CancellationToken cancellationToken)
        {
            await ActualView.PlayRush(
                unitAssetKeys,
                specialUnitAssetKey,
                specialUnitBonus,
                unpauseAction,
                battleSide,
                chargeCount,
                calculatedRushAttackPower,
                rushEvaluationType,
                cancellationToken);
        }

        public async UniTask PlayBattleStartNoiseAnimation(CancellationToken cancellationToken)
        {
            await ActualView.PlayBattleStartNoiseAnimation(cancellationToken);
        }

        public async UniTask TransformUnit(
            FieldObjectId beforeUnitId,
            FieldObjectId afterUnitId,
            CancellationToken cancellationToken)
        {
            await ActualView.TransformUnit(beforeUnitId, afterUnitId, cancellationToken);
        }

        public void SetUnitVisible(FieldObjectId id, bool visible)
        {
            ActualView.SetUnitVisible(id, visible);
        }

        public void SetUnitConditionVisible(bool isVisible)
        {
            ActualView.SetUnitConditionVisible(isVisible);
        }

        public FieldViewCoordV2 GetEnemyOutpostViewPos()
        {
            return ActualView.GetEnemyOutpostViewPos();
        }

        public FieldUnitView GetUnitView(FieldObjectId id)
        {
            return ActualView.GetUnitView(id);
        }

        public FieldViewCoordV2 GetUnitViewPos(FieldObjectId id)
        {
            return ActualView.GetUnitViewPos(id);
        }

        public FieldViewCoordV2 GetUnitViewTrackingPos(FieldObjectId id)
        {
            return ActualView.GetUnitViewTrackingPos(id);
        }

        public Vector2 GetEnemyOutpostKomaSetCenterPos()
        {
            return ActualView.GetEnemyOutpostKomaSetCenterPos();
        }

        public float GetFieldPageWidth()
        {
            return ActualView.FieldViewConstructData.TierViewWidth;
        }

        public void SetUnrestrictedScroll()
        {
            ActualView.SetUnrestrictedScroll();
        }

        public void SetElasticScroll()
        {
            ActualView.SetElasticScroll();
        }

        public bool IsDarknessKomaCleared(FieldViewCoordV2 position)
        {
            return ActualView.IsDarknessKomaCleared(position);
        }

        public void ShowDebugInfo()
        {
            ActualView.DebugInfoHidden = false;
        }

        public void HideHeaderButton()
        {
            ActualView.HideOptionButton();
            ActualView.HideAutoButton();
            ActualView.HideSpeedButton();
        }

        public void HideRushGauge()
        {
            ActualView.HideRushGauge();
        }

        public async UniTask LoadTutorialIntroductionManga(CancellationToken cancellationToken)
        {
            await ActualView.LoadTutorialIntroductionManga(cancellationToken);
        }

        public TutorialIntroductionMangaManager GetTutorialIntroductionMangaManager()
        {
            return ActualView.TutorialIntroductionMangaManager;
        }

        public void DisableRushAnimSkip()
        {
            ActualView.DisableRushAnimSkip();
        }

        bool IEscapeResponder.OnEscape()
        {
            // チュートリアル中はバックキーを無効にする
            if (InGameTutorialBackKeyHandler.IsPlayingTutorial())
            {
                // トーストでバックキーが無効であると表示する
                CommonToastWireFrame.ShowInvalidOperationMessage();
                return true;
            }

            // バトル中じゃない時はバックキーを無効にする
            if (!ViewDelegate.IsPlayingBattle())
            {
                // トーストでバックキーが無効であると表示する
                CommonToastWireFrame.ShowInvalidOperationMessage();
                return true;
            }

            if (ActualView.Hidden)
            {
                return false;
            }
            ViewDelegate.OnEscapeButtonTapped();
            return true;
        }

        [UIAction]
        void OnTitleBackTapped()
        {
            ViewDelegate.TransitToHome();
        }

        [UIAction]
        void OnMenuButtonTapped()
        {
            ViewDelegate.OnMenuButtonTapped();
        }

        [UIAction]
        void OnSkipButtonTapped()
        {
            ViewDelegate.OnSkipButtonTapped();
        }

        [UIAction]
        void OnRushButtonTapped()
        {
            ViewDelegate.OnRushButtonTapped();
        }

        [UIAction]
        void OnBattleSpeedButtonTapped()
        {
            ViewDelegate.OnBattleSpeedButtonTapped();
        }

        [UIAction]
        void OnAutoButtonTapped()
        {
            ViewDelegate.OnAutoButtonTapped();
        }
    }
}
