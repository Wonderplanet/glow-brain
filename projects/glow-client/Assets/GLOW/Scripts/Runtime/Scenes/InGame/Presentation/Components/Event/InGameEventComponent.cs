using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.BattleEndConditions;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class InGameEventComponent : UIObject
    {
        [SerializeField] InGameEnhanceQuestComponent _enhanceQuestComponent;
        [SerializeField] InGameTotalDefeatCountQuestComponent _totalDefeatCountQuestComponent;
        [SerializeField] InGameSpeedAttackQuestComponent _speedAttackQuestComponent;
        [SerializeField] InGameTimeLimitComponent _timeLimitComponent;
        [SerializeField] InGameDefenseTargetQuestComponent _defenseTargetQuestComponent;
        [SerializeField] InGameDefeatTargetEnemyQuestComponent _defeatTargetEnemyQuestComponent;
        [SerializeField] InGamePvpComponent _pvpComponent;

        IInGameStageTimeDelegate _stageTimeDelegate;
        IInGameStageScoreDelegate _stageScoreDelegate;
        IInGameDefeatEnemyCountDelegate _defeatEnemyCountDelegate;
        IInGameDefenseTargetDelegate _defenseTargetDelegate;
        IInGameDefeatTargetEnemyDelegate _defeatTargetEnemyDelegate;

        protected override void Awake()
        {
            base.Awake();
            _enhanceQuestComponent.Hidden = true;
            _totalDefeatCountQuestComponent.Hidden = true;
            _speedAttackQuestComponent.Hidden = true;
            _timeLimitComponent.Hidden = true;
            _defenseTargetQuestComponent.Hidden = true;
            _defeatTargetEnemyQuestComponent.Hidden = true;
            _pvpComponent.Hidden = true;
        }

        public void InitializeEnhanceQuest(StageTimeModel model)
        {
            _enhanceQuestComponent.Hidden = false;
            _enhanceQuestComponent.Initialize(model);
            _stageTimeDelegate = _enhanceQuestComponent;
            _stageScoreDelegate = _enhanceQuestComponent;
        }

        public void InitializeTotalDefeatCountQuest(DefeatedEnemyCountBattleEndConditionModel model)
        {
            _totalDefeatCountQuestComponent.Hidden = false;
            _totalDefeatCountQuestComponent.Initialize(model.DefeatedEnemyCount);
            _defeatEnemyCountDelegate = _totalDefeatCountQuestComponent;
        }

        public void InitializeSpeedAttackQuest(StageTimeModel model)
        {
            _speedAttackQuestComponent.Hidden = false;
            _speedAttackQuestComponent.Initialize(model);
            _stageTimeDelegate = _speedAttackQuestComponent;
        }

        public void InitializeTimeLimit(StageTimeModel model)
        {
            _timeLimitComponent.Hidden = false;
            _timeLimitComponent.Initialize(model);
            _stageTimeDelegate = _timeLimitComponent;
        }

        public void InitializeAdventBattle(StageTimeModel model)
        {
            _enhanceQuestComponent.Hidden = false;
            _enhanceQuestComponent.Initialize(model);
            _stageTimeDelegate = _enhanceQuestComponent;
            _stageScoreDelegate = _enhanceQuestComponent;
        }

        public void InitializeDefenseTargetQuest()
        {
            _defenseTargetQuestComponent.Hidden = false;
            _defenseTargetDelegate = _defenseTargetQuestComponent;
        }

        public void InitializeDefeatTargetEnemyQuest(DefeatUnitBattleEndConditionModel model)
        {
            _defeatTargetEnemyQuestComponent .Hidden = false;
            _defeatTargetEnemyQuestComponent.Initialize(model.CharacterName, model.DefeatEnemyCount);
            _defeatTargetEnemyDelegate = _defeatTargetEnemyQuestComponent;
        }

        public void InitializePvp(StageTimeModel model)
        {
            _pvpComponent.Hidden = false;
            _pvpComponent.Initialize(model);
            _stageTimeDelegate = _pvpComponent;
        }

        public void UpdateTimeLimit(StageTimeModel model)
        {
            _stageTimeDelegate?.UpdateTimeLimit(model);
        }

        public void UpdateScore(InGameScore score)
        {
            _stageScoreDelegate?.UpdateScore(score);
        }

        public void UpdateDefeatEnemyCount(DefeatEnemyCount defeatedCount, DefeatEnemyCount endCondition)
        {
            _defeatEnemyCountDelegate?.UpdateDefeatEnemyCount(defeatedCount, endCondition);
        }

        public void ShowDefenseTargetHighlight()
        {
            _defenseTargetDelegate?.ShowFrameAddEffect();
        }

        public void HideDefenseTargetHighlight()
        {
            _defenseTargetDelegate?.HideFrameAddEffect();
        }

        public void UpdateDefeatTargetEnemyProgress(DefeatEnemyCount defeatedCount, DefeatEnemyCount endCondition)
        {
            _defeatTargetEnemyDelegate?.UpdateRemainingEnemyCount(defeatedCount, endCondition);
        }
    }
}
