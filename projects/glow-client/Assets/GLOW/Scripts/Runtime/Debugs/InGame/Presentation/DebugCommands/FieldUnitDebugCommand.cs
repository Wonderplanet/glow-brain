#if GLOW_INGAME_DEBUG
using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Debugs.Command.Presentations.Presenters;
using GLOW.Debugs.InGame.Domain.Models;
using Cysharp.Text;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Debugs.InGame.Presentation.DebugCommands
{
    public static class FieldUnitDebugCommand
    {
        public static void AddFieldUnitListButton(
            IDebugCommandPresenter presenter,
            IEnumerable<DebugFieldUnitInfoModel> unitInfos,
            Action<DebugFieldUnitInfoModel, StateEffect> onApplyStateEffect,
            Action<DebugFieldUnitInfoModel, DebugUnitStatusModel> onApplyStatus = null)
        {
            presenter.AddNestedMenuButton("ユニット操作", nestedPresenter =>
            {
                foreach (var unitInfo in unitInfos)
                {
                    AddUnitButton(nestedPresenter, unitInfo, onApplyStateEffect, onApplyStatus);
                }
            });
        }

        static void AddUnitButton(
            IDebugCommandPresenter presenter,
            DebugFieldUnitInfoModel unitInfo,
            Action<DebugFieldUnitInfoModel, StateEffect> onApplyStateEffect,
            Action<DebugFieldUnitInfoModel, DebugUnitStatusModel> onApplyStatus)
        {
            var displayName = unitInfo.UnitKind is CharacterUnitKind.Boss or CharacterUnitKind.AdventBattleBoss
                ? ZString.Format("[強]{0}", unitInfo.Name)
                : unitInfo.Name.ToString();

            var label = ZString.Format("[{0}] {1}", unitInfo.FieldObjectId, displayName);
            presenter.AddNestedMenuButton(label, nestedPresenter =>
            {
                AddStateEffectButton(nestedPresenter, unitInfo, onApplyStateEffect);
                AddStatusButton(nestedPresenter, unitInfo, onApplyStatus);
            });
        }

        static void AddStatusButton(
            IDebugCommandPresenter presenter,
            DebugFieldUnitInfoModel unitInfo,
            Action<DebugFieldUnitInfoModel, DebugUnitStatusModel> onApplyStatus)
        {
            presenter.AddNestedMenuButton("ステータス", nestedPresenter =>
            {
                string maxHp = unitInfo.Status.MaxHp.Value.ToString();
                string hp = unitInfo.Status.Hp.Value.ToString();
                string attackPower = unitInfo.Status.AttackPower.Value.ToString();

                nestedPresenter.AddTextBox("最大HP", maxHp, v => maxHp = v);
                nestedPresenter.AddTextBox("HP", hp, v => hp = v);
                nestedPresenter.AddTextBox("攻撃力", attackPower, v => attackPower = v);

                nestedPresenter.AddButton("適用", () =>
                {
                    int.TryParse(maxHp, out var parsedMaxHp);
                    int.TryParse(hp, out var parsedHp);
                    decimal.TryParse(attackPower, out var parsedAttackPower);

                    onApplyStatus?.Invoke(
                        unitInfo,
                        new DebugUnitStatusModel(
                            new HP(parsedMaxHp),
                            new HP(parsedHp),
                            new AttackPower(parsedAttackPower))
                    );
                });
            });
        }

        static void AddStateEffectButton(
            IDebugCommandPresenter presenter,
            DebugFieldUnitInfoModel unitInfo,
            Action<DebugFieldUnitInfoModel, StateEffect> onApplyStateEffect)
        {
            presenter.AddNestedMenuButton("状態変化付与", nestedPresenter =>
            {
                foreach (StateEffectType effectType in Enum.GetValues(typeof(StateEffectType)).Cast<StateEffectType>())
                {
                    if (effectType == StateEffectType.None) continue;
                    AddStateEffectInputMenu(nestedPresenter, unitInfo, effectType, onApplyStateEffect);
                }
            });
        }

        static void AddStateEffectInputMenu(
            IDebugCommandPresenter presenter,
            DebugFieldUnitInfoModel unitInfo,
            StateEffectType effectType,
            Action<DebugFieldUnitInfoModel, StateEffect> onApplyStateEffect)
        {
            var config = StateEffectInputConfig.Empty with
            {
                EffectTypeLabel = effectType.ToString()
            };
            
            if (StateEffectInputConfigMap.Map.TryGetValue(effectType, out var matchedConfig))
            {
                config = matchedConfig;
            }

            presenter.AddNestedMenuButton(config.EffectTypeLabel, nestedPresenter =>
            {
                string effectiveCount = "-1";
                string effectiveProbability = "100";
                string duration = "-1";
                string parameter = config.ParameterDefault;
                string conditionValue1 = config.ConditionValue1Default;
                string conditionValue2 = config.ConditionValue2Default;

                nestedPresenter.AddTextBox("発動回数", effectiveCount, v => effectiveCount = v);
                nestedPresenter.AddTextBox("発動確率", effectiveProbability, v => effectiveProbability = v);
                nestedPresenter.AddTextBox("持続時間(s)", duration, v => duration = v);
                nestedPresenter.AddTextBox(config.ParameterLabel, parameter, v => parameter = v);
                nestedPresenter.AddTextBox(config.ConditionValue1Label, conditionValue1, v => conditionValue1 = v);
                nestedPresenter.AddTextBox(config.ConditionValue2Label, conditionValue2, v => conditionValue2 = v);

                nestedPresenter.AddButton("適用", () =>
                {
                    int.TryParse(effectiveCount, out var parsedEffectiveCount);
                    int.TryParse(effectiveProbability, out var parsedEffectiveProbability);
                    int.TryParse(duration, out var parsedDuration);
                    int.TryParse(parameter, out var parsedParameter);

                    var effect = new StateEffect(
                        effectType,
                        effectiveCount == "-1"
                            ? EffectiveCount.Infinity
                            : new EffectiveCount(parsedEffectiveCount),
                        new EffectiveProbability(parsedEffectiveProbability),
                        duration == "-1"
                            ? TickCount.Infinity
                            : TickCount.FromSeconds(parsedDuration),
                        new StateEffectParameter(parsedParameter),
                        new StateEffectConditionValue(conditionValue1),
                        new StateEffectConditionValue(conditionValue2)
                    );
                    onApplyStateEffect?.Invoke(unitInfo, effect);
                });
            });
        }
    }
}
#endif
