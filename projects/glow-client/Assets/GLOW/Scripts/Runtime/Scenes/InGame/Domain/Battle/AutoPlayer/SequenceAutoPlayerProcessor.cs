using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Extensions;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.CommonConditions;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle.AutoPlayer
{
    public class SequenceAutoPlayerProcessor : IAutoPlayerProcessor
    {
        BattleSide _battleSide;
        SequenceGroupSwitchableAutoPlayerSequencer _autoPlayerSequencer;

        TickCount _elapsedTickCount = TickCount.Empty;
        MstPageModel _mstPageModel = MstPageModel.Empty;
        IStageEnemyParameterCoef _stageEnemeyParameterCoef = MstStageModel.Empty;
        IInitialCharacterUnitCoefFactory _coefFactory;
        AutoPlayerSequenceSummonCount _bossCount = AutoPlayerSequenceSummonCount.Empty;

        public AutoPlayerSequenceGroupModel CurrentAutoPlayerSequenceGroupModel => _autoPlayerSequencer.CurrentSequenceGroupModel;
        public AutoPlayerSequenceSummonCount BossCount => _bossCount;

        public void Setup(
            AutoPlayerSequenceModel autoPlayerSequenceModel,
            BattleSide battleSide,
            MstPageModel mstPageModel,
            IStageEnemyParameterCoef stageEnemyParameterCoef,
            IAutoPlayerSequenceElementStateModelFactory stateModelFactory,
            IInitialCharacterUnitCoefFactory coefFactory)
        {
            var elementStateModels =
                autoPlayerSequenceModel.MstAutoPlayerSequenceModel.Elements
                    .Select(model => stateModelFactory.Create(model, battleSide))
                    .ToList();

            _autoPlayerSequencer =
                new SequenceGroupSwitchableAutoPlayerSequencer(elementStateModels);

            _battleSide = battleSide;
            _elapsedTickCount = new TickCount(0);
            _mstPageModel = mstPageModel;
            _stageEnemeyParameterCoef = stageEnemyParameterCoef;
            _coefFactory = coefFactory;
            _bossCount = autoPlayerSequenceModel.BossCount;
        }

        public IReadOnlyList<IAutoPlayerAction> Tick(AutoPlayerTickContext context)
        {
            var unitGenerationModelFactory = context.UnitGenerationModelFactory;
            var commonConditionContext = context.CommonConditionContext;
            var tickCount = context.TickCount;

            // tickCount増やす
            _elapsedTickCount += tickCount;

            // 順番依存.1
            var elementStateModels = _autoPlayerSequencer.CurrentAutoPlayerSequenceElementStateModels;
            var updatedElementStateModelsAtActivatedFlag = UpdateActivationFlags(elementStateModels, commonConditionContext);
            _autoPlayerSequencer.SetCurrentSequenceElementStateModels(updatedElementStateModelsAtActivatedFlag);

            // 順番依存.2
            var updatedElementStateModelsAtActionDelay = UpdateActionDelay(
                updatedElementStateModelsAtActivatedFlag,
                tickCount);
            _autoPlayerSequencer.SetCurrentSequenceElementStateModels(updatedElementStateModelsAtActionDelay);

            // 順番依存.3
            List<IAutoPlayerAction> autoPlayerActions = new List<IAutoPlayerAction>();
            var updatedSummonStatusAndSummonActions = UpdateSummonStatus(
                updatedElementStateModelsAtActionDelay,
                unitGenerationModelFactory);
            _autoPlayerSequencer.SetCurrentSequenceElementStateModels(updatedSummonStatusAndSummonActions.elements);
            autoPlayerActions.AddRange(updatedSummonStatusAndSummonActions.actions);

            var updatedTransformActions = UpdateTransformStatus(
                updatedSummonStatusAndSummonActions.elements,
                unitGenerationModelFactory);
            _autoPlayerSequencer.SetCurrentSequenceElementStateModels(updatedTransformActions.elements);
            autoPlayerActions.AddRange(updatedTransformActions.actions);

            // actionでSequenceGroupがいたらswitch
            if (TryGetSwitchSequenceGroup(out var nextGroupId))
            {
                _autoPlayerSequencer.SwitchSequenceGroup(nextGroupId, _elapsedTickCount);
            }

            return autoPlayerActions;
        }


        /// <summary>
        /// ゲートHPへのダメージが条件で召喚されるキャラが残っているか
        /// </summary>
        /// <returns></returns>
        public bool RemainsSummonUnitByOutpostDamage()
        {
            return _autoPlayerSequencer.CurrentAutoPlayerSequenceElementStateModels
                .Any(element => !element.IsActivated && element.ActivationCondition.ConditionType.IsEnemyOutpostDamageCondition());
        }

        List<AutoPlayerSequenceElementStateModel> UpdateActivationFlags(
            IReadOnlyList<AutoPlayerSequenceElementStateModel> elementStateModels,
            CommonConditionContext commonConditionContext)
        {
            var result = new List<AutoPlayerSequenceElementStateModel>();
            var activateElementStateModels = new List<AutoPlayerSequenceElementStateModel>();

            foreach (var elementModel in elementStateModels)
            {
                // 両方とも活性化ずみ
                if (elementModel.IsActivated && elementModel.IsDeactivated)
                {
                    result.Add(elementModel);
                    continue;
                }

                var isActivated = elementModel.IsActivated;
                if (!elementModel.IsActivated)
                {
                    var meetsCondition = MeetsCondition(elementModel.ActivationCondition, commonConditionContext);
                    if (meetsCondition)
                    {
                        isActivated = AutoPlayerSequenceElementActivatedFlag.True;
                    }
                }

                // 活性化条件を満たしている場合に非活性化の条件を確認していく
                var isDeactivated = elementModel.IsDeactivated;
                if (isActivated && !elementModel.IsDeactivated)
                {
                    var meetsCondition = MeetsCondition(elementModel.DeactivationCondition, commonConditionContext);
                    if (meetsCondition)
                    {
                        isDeactivated = AutoPlayerSequenceElementDeactivatedFlag.True;
                    }
                }

                // 更新無しならそのまま追加
                if (isActivated == elementModel.IsActivated &&
                    isDeactivated == elementModel.IsDeactivated)
                {
                    result.Add(elementModel);
                    continue;
                }

                var updateElementModel = elementModel with
                {
                    IsActivated = isActivated,
                    IsDeactivated = isDeactivated
                };

                // 今回で活性したものは一旦別リストに設定し、後ほど複数Sequence同時達成時の非活性判定を行ってからresultにマージする
                if (isActivated && !elementModel.IsActivated)
                {
                    activateElementStateModels.Add(updateElementModel);
                    continue;
                }

                result.Add(updateElementModel);
            }

            // 今回同時活性したSequenceの中に活性化の優先IDのSequenceがあったら非活性化するフラグ値を元に不活性対応を行ってからresultに加算する
            foreach (var elementModel in activateElementStateModels)
            {
                if (elementModel.ElementModel.PrioritySequenceElementId.IsEmpty())
                {
                    result.Add(elementModel);
                    continue;
                }

                var isDeactivation = activateElementStateModels.Any(anotherElement =>
                    anotherElement.ElementModel.SequenceElementId == elementModel.ElementModel.PrioritySequenceElementId);
                if (isDeactivation)
                {
                    var updateElementModel = elementModel with
                    {
                        IsDeactivated = AutoPlayerSequenceElementDeactivatedFlag.True,
                    };
                    result.Add(updateElementModel);
                    continue;
                }

                result.Add(elementModel);
            }

            return result;
        }

        /// <summary> 活性化後の実行開始までの遅延実行時間更新 </summary>
        List<AutoPlayerSequenceElementStateModel> UpdateActionDelay(
            IReadOnlyList<AutoPlayerSequenceElementStateModel> elementStateModels,
            TickCount tickCount)
        {
            var updatedSequenceElementStateModels = new List<AutoPlayerSequenceElementStateModel>();

            foreach (var elementStateModel in elementStateModels)
            {
                // 非活性にあたる状態か、すでに遅延時間がなければそのまま追加
                if (!elementStateModel.IsActivated ||
                    elementStateModel.IsDeactivated ||
                    elementStateModel.RemainingActionDelay <= TickCount.Zero)
                {
                    updatedSequenceElementStateModels.Add(elementStateModel);
                    continue;
                }

                // 遅延実行の時間が残っていれば、RemainingActionDelayを減らして追加
                var updateRemainingActionDelay = elementStateModel.RemainingActionDelay - tickCount;
                updatedSequenceElementStateModels.Add(elementStateModel with
                {
                    RemainingActionDelay = updateRemainingActionDelay
                });
            }

            return updatedSequenceElementStateModels;
        }

        bool TryGetSwitchSequenceGroup(out AutoPlayerSequenceGroupId nextGroupId)
        {
            nextGroupId = AutoPlayerSequenceGroupId.Empty;
            var sequenceGroupElements =
                _autoPlayerSequencer.CurrentAutoPlayerSequenceElementStateModels
                .Where(e => e.ElementModel.Action.Type == AutoPlayerSequenceActionType.SwitchSequenceGroup)
                .Where(e => e.IsActivated)
                .Where(e => !e.IsDeactivated)
                .Where(e => e.RemainingActionDelay <= TickCount.Zero)
                .ToList();

            var shouldSwitch = sequenceGroupElements.Any();
            if(shouldSwitch) nextGroupId = sequenceGroupElements.First().ElementModel.Action.Value.ToAutoPlayerSequenceGroupId();
            return shouldSwitch;
        }

        (List<IAutoPlayerAction> actions, List<AutoPlayerSequenceElementStateModel> elements)
            UpdateSummonStatus(IReadOnlyList<AutoPlayerSequenceElementStateModel> elementStateModels,IUnitGenerationModelFactory unitGenerationModelFactory)
        {
            var updatedSequenceElementStateModels = new List<AutoPlayerSequenceElementStateModel>();
            var actions = new List<IAutoPlayerAction>();

            foreach (var elementStateModel in elementStateModels)
            {
                // アクティブで無ければそのまま追加
                if (!elementStateModel.IsActivated)
                {
                    updatedSequenceElementStateModels.Add(elementStateModel);
                    continue;
                }

                // アクティブになった状態で非活性化の条件を満たしていたらそのまま追加
                if (elementStateModel.IsDeactivated)
                {
                    updatedSequenceElementStateModels.Add(elementStateModel);
                    continue;
                }

                // 遅延実行の時間が残っていればそのまま追加
                if (elementStateModel.RemainingActionDelay > TickCount.Zero)
                {
                    updatedSequenceElementStateModels.Add(elementStateModel);
                    continue;
                }

                // 召喚系でなければそのまま追加
                if(elementStateModel.ElementModel.Action.Type != AutoPlayerSequenceActionType.SummonEnemy &&
                   elementStateModel.ElementModel.Action.Type != AutoPlayerSequenceActionType.SummonPlayerCharacter &&
                   elementStateModel.ElementModel.Action.Type != AutoPlayerSequenceActionType.SummonGimmickObject)
                {
                    updatedSequenceElementStateModels.Add(elementStateModel);
                    continue;
                }

                // 召喚回数が0ならそのまま追加
                var elementModel = elementStateModel.ElementModel;
                if (elementStateModel.RemainingSummonCount.IsZero())
                {
                    updatedSequenceElementStateModels.Add(elementStateModel);
                    continue;
                }

                // 召喚間隔を減らす
                var updatedRemainingSummonInterval = elementStateModel.RemainingSummonInterval - new TickCount(1);
                var updatedRemainingSummonCount = elementStateModel.RemainingSummonCount;

                // 召喚処理
                if (updatedRemainingSummonInterval.IsZero())
                {
                    actions.Add(CreateAction(elementModel, unitGenerationModelFactory));

                    // 召喚間隔リセットと可能回数減らす
                    updatedRemainingSummonInterval = elementModel.SummonInterval;
                    updatedRemainingSummonCount -= 1;
                }

                // 召喚処理後の対象追加
                updatedSequenceElementStateModels.Add(elementStateModel with
                {
                    RemainingSummonInterval = updatedRemainingSummonInterval,
                    RemainingSummonCount = updatedRemainingSummonCount,
                });
            }

            return (actions, updatedSequenceElementStateModels);
        }

        /// <summary> 変換系のシークエンスの更新 </summary>
        (List<IAutoPlayerAction> actions, List<AutoPlayerSequenceElementStateModel> elements)
            UpdateTransformStatus(
                IReadOnlyList<AutoPlayerSequenceElementStateModel> elementStateModels,
                IUnitGenerationModelFactory unitGenerationModelFactory)
        {
            var updatedSequenceElementStateModels = new List<AutoPlayerSequenceElementStateModel>();
            var actions = new List<IAutoPlayerAction>();

            foreach (var elementStateModel in elementStateModels)
            {
                // アクティブで無ければそのまま追加
                if (!elementStateModel.IsActivated)
                {
                    updatedSequenceElementStateModels.Add(elementStateModel);
                    continue;
                }

                // アクティブになった状態で非活性化の条件を満たしていたらそのまま追加
                if (elementStateModel.IsDeactivated)
                {
                    updatedSequenceElementStateModels.Add(elementStateModel);
                    continue;
                }

                // 遅延実行の時間が残っていればそのまま追加
                if (elementStateModel.RemainingActionDelay > TickCount.Zero)
                {
                    updatedSequenceElementStateModels.Add(elementStateModel);
                    continue;
                }

                // 変換系の処理でなければ
                if (elementStateModel.ElementModel.Action.Type != AutoPlayerSequenceActionType.TransformGimmickObjectToEnemy)
                {
                    updatedSequenceElementStateModels.Add(elementStateModel);
                    continue;
                }

                actions.Add(CreateAction(elementStateModel.ElementModel, unitGenerationModelFactory));
                updatedSequenceElementStateModels.Add(elementStateModel with
                {
                    // ギミックオブジェクトは初期配置のみのため一度CreateActionした後は
                    // 毎回変換のActionが生成されないようにシークエンスは非活性にした上で追加
                    IsDeactivated = AutoPlayerSequenceElementDeactivatedFlag.True,
                });
            }

            return (actions, updatedSequenceElementStateModels);
        }

        bool MeetsCondition(
            ICommonConditionModel conditionModel,
            CommonConditionContext commonConditionContext)
        {
            return conditionModel.MeetsCondition(commonConditionContext);
        }

        IAutoPlayerAction CreateAction(
            MstAutoPlayerSequenceElementModel sequenceElement,
            IUnitGenerationModelFactory unitGenerationModelFactory)
        {
            return sequenceElement.Action.Type switch
            {
                AutoPlayerSequenceActionType.SummonEnemy =>
                    new AutoPlayerSummonEnemyAction(
                        sequenceElement.Action.Value.ToMasterDataId(),
                        unitGenerationModelFactory.Create(sequenceElement, _battleSide, _stageEnemeyParameterCoef, _coefFactory)),

                AutoPlayerSequenceActionType.SummonPlayerCharacter =>
                    new AutoPlayerSummonDeckUnitAction(sequenceElement.Action.Value.ToDeckUnitIndex()),

                // ギミックオブジェクトは初期配置のみ有効とする
                AutoPlayerSequenceActionType.SummonGimmickObject => AutoPlayerEmptyAction.Instance,

                AutoPlayerSequenceActionType.TransformGimmickObjectToEnemy =>
                    new AutoPlayerTransformGimmickObjectToEnemyAction(
                        sequenceElement.Action.Value.ToMasterDataId(),
                        unitGenerationModelFactory.Create(sequenceElement, _battleSide, _stageEnemeyParameterCoef, _coefFactory),
                        sequenceElement.Action.Value2.ToAutoPlayerSequenceElementId()),

                _ => AutoPlayerEmptyAction.Instance
            };
        }
    }
}
