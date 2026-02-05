#if GLOW_INGAME_DEBUG
using System;
using System.Collections.Generic;
using Cysharp.Text;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Extensions;
using GLOW.Debugs.Command.Presentations.Presenters;
using GLOW.Debugs.Home.Domain.Constants;
using GLOW.Debugs.InGame.Domain.Models;
using GLOW.Debugs.InGame.Domain.UseCases;
using GLOW.Debugs.InGame.Presentation.DebugIngameLogView;
using GLOW.Modules.MessageView.Presentation;
using GLOW.Scenes.InGame.Domain.AssetLoaders;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ScriptableObjects;
using GLOW.Scenes.InGame.Domain.UseCases;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Scenes.InGame.Presentation.Views;
using WonderPlanet.ToastNotifier;
using WPFramework.Presentation.Modules;
using Zenject;

namespace GLOW.Debugs.InGame.Presentation.DebugCommands
{
    public class InGameDebugCommandHandler
    {
        [Inject] DebugGetDebugModelUseCase DebugGetDebugModelUseCase { get; }
        [Inject] DebugMaximizeBattlePointUseCase DebugMaximizeBattlePointUseCase { get; }
        [Inject] DebugChangeSummonCostToZeroUseCase DebugSetSummonCostToZeroUseCase { get; }
        [Inject] DebugResetSummonCostUseCase DebugResetSummonCostUseCase { get; }
        [Inject] DebugToggleBattlePauseUseCase DebugToggleBattlePauseUseCase { get; }
        [Inject] DebugChangeSpecialAttackCoolTimeToZeroUseCase DebugChangeSpecialAttackCoolTimeToZeroUseCase { get; }
        [Inject] DebugResetSpecialAttackCoolTimeUseCase DebugResetSpecialAttackCoolTimeUseCase { get; }
        [Inject] DebugVictoryUseCase DebugVictoryUseCase { get; }
        [Inject] DebugDefeatUseCase DebugDefeatUseCase { get; }
        [Inject] DebugChangeCharacterUnitDamageInvalidationUseCase DebugCharacterUnitDamageInvalidationUseCase { get; }
        [Inject] DebugSummonEnemyUnitUseCase DebugSummonEnemyUnitUseCase { get; }
        [Inject] DebugApplyStateEffectUseCase DebugApplyStateEffectUseCase { get; }
        [Inject] DebugChangeUnitStatusUseCase DebugChangeUnitStatusUseCase { get; }
        [Inject] DebugChangeOutpostDamageInvalidationUseCase DebugChangeOutpostDamageInvalidationUseCase { get; }
        [Inject] DebugChangeOutpostEnhancementUseCase DebugChangeOutpostEnhancementUseCase { get; }
        [Inject] DebugSetUnitHpToZeroUseCase DebugSetUnitHpToZeroUseCase { get; }
        [Inject] DebugDisableKnockBackUseCase DebugDisableKnockBackUseCase { get; }
        [Inject] DebugAlwaysEnableRushUseCase DebugAlwaysEnableRushUseCase { get; }
        [Inject] DebugGetCharacterUnits DebugGetCharacterUnits { get; }
        [Inject] DebugToggleBattleStageTimePauseUseCase DebugToggleBattleStageTimePauseUseCase { get; }
        [Inject] IMessageViewUtil MessageViewUtil { get; }
        [Inject] IViewFactory ViewFactory { get; }
        [Inject] IInGameScene InGameScene { get; }
        [Inject] InGameViewController ViewController { get; }
        [Inject] IUnitAttackViewInfoSetContainer UnitAttackViewInfoSetContainer { get; }

        bool _isDebugViewDisplaying;

        public Action<CharacterUnitModel, UnitAttackViewInfo> PlayDebugCutIn { get; set; }
        public Action<bool> DebugPause { get; set; }

        public void CreateDebugCommandRootMenu(IDebugCommandPresenter debugCommandPresenter)
        {
            // デバッグメニューを構築
            var debugModel = DebugGetDebugModelUseCase.GetDebugModel();

            debugCommandPresenter.AddButton(
                "デバッグ情報表示",
                () =>
                {
                    ViewController.ShowDebugInfo();
                });

            debugCommandPresenter.AddButton(
                "出現ユニット・デッキ情報表示",
                () =>
                {
                    if (_isDebugViewDisplaying)
                    {
                        Toast.MakeText("既に表示されています").Show();
                        return;
                    }

                    var controller = ViewFactory
                        .Create<DebugIngameLogViewerViewController, DebugIngameLogViewerViewController.Argument>(
                            new DebugIngameLogViewerViewController.Argument
                            {
                                OnClose = () => _isDebugViewDisplaying = false
                            });

                    ViewController.Show(controller);
                    _isDebugViewDisplaying = true;

                    Toast.MakeText("ユニット情報画面を表示しました").Show();
                });

            debugCommandPresenter.AddButton(
                "勝利する",
                () =>
                {
                    debugCommandPresenter.CloseMenu();
                    DebugVictoryUseCase.Victory();
                });

            debugCommandPresenter.AddButton(
                "敗北する",
                () =>
                {
                    debugCommandPresenter.CloseMenu();
                    DebugDefeatUseCase.Defeat();
                });

            debugCommandPresenter.AddButton(
                "味方キャラ撃破",
                () =>
                {
                    debugCommandPresenter.CloseMenu();
                    DebugSetUnitHpToZeroUseCase.SetUnitHpToZero(BattleSide.Player);
                    Toast.MakeText("味方キャラのHPを0にしました").Show();
                });

            debugCommandPresenter.AddButton(
                "敵キャラ撃破",
                () =>
                {
                    debugCommandPresenter.CloseMenu();
                    DebugSetUnitHpToZeroUseCase.SetUnitHpToZero(BattleSide.Enemy);
                    Toast.MakeText("敵キャラのHPを0にしました").Show();
                });

            debugCommandPresenter.AddButton(
                "リーダーp を最大にする",
                () =>
                {
                    DebugMaximizeBattlePointUseCase.MaximizeBattlePoint();
                    Toast.MakeText("リーダーpを最大にしました").Show();
                });

            debugCommandPresenter.AddToggleButton(
                "召喚コスト0", debugModel.IsZeroSummonCost,
                isOn =>
                {
                    if (isOn)
                    {
                        DebugSetSummonCostToZeroUseCase.SetSummonCoolTimeToZero();
                        Toast.MakeText("召喚コストを0にしました").Show();
                    }
                    else
                    {
                        DebugResetSummonCostUseCase.ResetSummonCoolTime();
                        Toast.MakeText("召喚コストを元に戻しました").Show();
                    }
                });

            debugCommandPresenter.AddToggleButton(
                "必殺技クールタイム0", debugModel.IsZeroSpecialAttackCoolTime,
                isOn =>
                {
                    if (isOn)
                    {
                        DebugChangeSpecialAttackCoolTimeToZeroUseCase.SetSpecialAttackCoolTimeToZero();
                        Toast.MakeText("必殺技クールタイムを0にしました").Show();
                    }
                    else
                    {
                        DebugResetSpecialAttackCoolTimeUseCase.ResetSpecialAttackCoolTime();
                        Toast.MakeText("必殺技クールタイムを元に戻しました").Show();
                    }
                });

            debugCommandPresenter.AddButton(
                "通常ノックバック無効",
                () =>
                {
                    DebugDisableKnockBackUseCase.DisableKnockBack(includeForcedKnockBack:false);
                    debugCommandPresenter.CloseMenu();
                    Toast.MakeText("全ユニットの通常ノックバックを無効化しました").Show();
                });

            debugCommandPresenter.AddButton(
                "全ノックバック無効",
                () =>
                {
                    DebugDisableKnockBackUseCase.DisableKnockBack(includeForcedKnockBack:true);
                    debugCommandPresenter.CloseMenu();
                    Toast.MakeText("全ユニットの全ノックバックを無効化しました").Show();
                });

            debugCommandPresenter.AddToggleButton(
                "味方ダメージ無効", debugModel.IsPlayerUnitDamageInvalidation,
                isOn =>
                {
                    if (isOn)
                    {
                        DebugCharacterUnitDamageInvalidationUseCase.ChangeCharacterUnitDamageInvalidation(
                            BattleSide.Player,
                            DamageInvalidationFlag.True);
                        Toast.MakeText("味方ダメージを無効化しました").Show();
                    }
                    else
                    {
                        DebugCharacterUnitDamageInvalidationUseCase.ChangeCharacterUnitDamageInvalidation(
                            BattleSide.Player,
                            DamageInvalidationFlag.False);
                        Toast.MakeText("味方ダメージ無効化を解除しました").Show();
                    }
                });

            debugCommandPresenter.AddToggleButton(
                "敵ダメージ無効", debugModel.IsEnemyUnitDamageInvalidation,
                isOn =>
                {
                    if (isOn)
                    {
                        DebugCharacterUnitDamageInvalidationUseCase.ChangeCharacterUnitDamageInvalidation(
                            BattleSide.Enemy,
                            DamageInvalidationFlag.True);
                        Toast.MakeText("敵ダメージを無効化しました").Show();
                    }
                    else
                    {
                        DebugCharacterUnitDamageInvalidationUseCase.ChangeCharacterUnitDamageInvalidation(
                            BattleSide.Enemy,
                            DamageInvalidationFlag.False);
                        Toast.MakeText("敵ダメージ無効化を解除しました").Show();
                    }
                });

            debugCommandPresenter.AddToggleButton(
                "味方ゲートダメージ無効", debugModel.IsPlayerOutpostDamageInvalidation,
                isOn =>
                {
                    if (isOn)
                    {
                        DebugChangeOutpostDamageInvalidationUseCase.SetOutpostDamageInvalidation(
                            BattleSide.Player,
                            OutpostDamageInvalidationFlag.True);
                        Toast.MakeText("味方ゲートダメージを無効化しました").Show();
                    }
                    else
                    {
                        DebugChangeOutpostDamageInvalidationUseCase.SetOutpostDamageInvalidation(
                            BattleSide.Player,
                            OutpostDamageInvalidationFlag.False);
                        Toast.MakeText("味方ゲートダメージ無効化を解除しました").Show();
                    }
                });

            debugCommandPresenter.AddToggleButton(
                "敵ゲートダメージ無効", debugModel.IsEnemyOutpostDamageInvalidation,
                isOn =>
                {
                    if (isOn)
                    {
                        DebugChangeOutpostDamageInvalidationUseCase.SetOutpostDamageInvalidation(
                            BattleSide.Enemy,
                            OutpostDamageInvalidationFlag.True);
                        Toast.MakeText("敵ゲートダメージを無効化しました").Show();
                    }
                    else
                    {
                        DebugChangeOutpostDamageInvalidationUseCase.SetOutpostDamageInvalidation(
                            BattleSide.Enemy,
                            OutpostDamageInvalidationFlag.False);
                        Toast.MakeText("敵ゲートダメージ無効化を解除しました").Show();
                    }
                });

            debugCommandPresenter.AddButton(
                "ゲート強化値 表示",
                () =>
                {
                    var stringBuilder = ZString.CreateStringBuilder();
                    foreach (var enhancement in debugModel.OutpostEnhancement.EnhancementElements)
                    {
                        stringBuilder.AppendLine(ZString.Format("{0}：{1}",
                            enhancement.Type.ToDisplayString(),
                            enhancement.Value.Value));
                    }

                    MessageViewUtil.ShowMessageWithOk(
                        "ゲート強化値(強化補正値のみ)",
                        stringBuilder.ToString(),
                        string.Empty,
                        () => { });
                });

            // ゲート強化値変更
            OutpostEnhancementDebugCommand.AddOutpostEnhancementButton(
                debugCommandPresenter,
                debugModel.OutpostEnhancement,
                enhancementDictionary =>
                {
                    DebugChangeOutpostEnhancementUseCase.ChangeEnhancementValue(enhancementDictionary);
                    debugCommandPresenter.CloseMenu();
                    Toast.MakeText("ゲート強化値を変更しました").Show();
                });

            debugCommandPresenter.AddNestedMenuButton(
                "ファントム召喚",
                presenter => CreateEnemySummonMenu(presenter, debugModel.DebugEnemyInfos));

            // ユニット操作
            FieldUnitDebugCommand.AddFieldUnitListButton(
                debugCommandPresenter,
                debugModel.FieldUnitInfos,
                (unitInfo, stateEffect) =>
                {
                    DebugApplyStateEffectUseCase.ApplyStateEffect(unitInfo.FieldObjectId, stateEffect);
                    Toast.MakeText("状態変化を付与しました").Show();
                },
                (unitInfo, status) =>
                {
                    DebugChangeUnitStatusUseCase.ChangeStatus(unitInfo.FieldObjectId, status);
                    Toast.MakeText("ステータスを適用しました").Show();
                });

            // ラッシュ常時発動
            debugCommandPresenter.AddNestedMenuButton(
                "ラッシュ常時発動",
                presenter =>
                {
                    var chargeCount = 3;

                    presenter.AddTextBox(
                        "チャージ数",
                        chargeCount.ToString(),
                        text =>
                        {
                            int.TryParse(text, out chargeCount);
                        });
                    presenter.AddButton(
                        "適用",
                        () =>
                        {
                            DebugAlwaysEnableRushUseCase.EnableAlwaysRush(new RushChargeCount(chargeCount));
                            debugCommandPresenter.CloseMenu();
                            Toast.MakeText("ラッシュ常時発動を適用しました").Show();
                        });
                });

            debugCommandPresenter.AddButton(
                "カットイン",
                () =>
                {
                    var unitModels = DebugGetCharacterUnits.GetCharacterUnits();
                    var unitModel = unitModels.FirstOrDefault(
                        model => model.BattleSide == BattleSide.Player,
                        CharacterUnitModel.Empty);

                    var unitAttackViewInfoSet = UnitAttackViewInfoSetContainer.GetUnitAttackViewInfo(unitModel.AssetKey);
                    if (unitAttackViewInfoSet == null) return;

                    var attackViewInfo = unitAttackViewInfoSet.SpecialAttackViewInfo;
                    if (attackViewInfo.CutInPrefab_background == null) return;

                    PlayDebugCutIn?.Invoke(unitModel, attackViewInfo);

                    debugCommandPresenter.CloseMenu();
                });

            debugCommandPresenter.AddToggleButton(
                "バトルポーズ", debugModel.IsBattlePaused,
                isOn =>
                {
                    DebugToggleBattlePauseUseCase.SetBattlePause(isOn);
                    DebugPause?.Invoke(isOn);

                    debugCommandPresenter.CloseMenu();

                    if (isOn)
                    {
                        Toast.MakeText("バトルを一時停止しました").Show();
                    }
                    else
                    {
                        Toast.MakeText("バトルを再開しました").Show();
                    }
                });

            debugCommandPresenter.AddToggleButton(
                "時間制限付きステージの時間経過停止",
                debugModel.StageTimeSpeed.IsZero(),
                isOn =>
                {
                    DebugToggleBattleStageTimePauseUseCase.SetBattleStageTimePause(isOn);

                    debugCommandPresenter.CloseMenu();

                    if (isOn)
                    {
                        Toast.MakeText("時間経過を停止しました").Show();
                    }
                    else
                    {
                        Toast.MakeText("時間経過を再開しました").Show();
                    }
                });
        }

        void CreateEnemySummonMenu(
            IDebugCommandPresenter debugCommandPresenter,
            IReadOnlyList<DebugEnemyInfoModel> enemyInfos)
        {
            foreach (var info in enemyInfos)
            {
                var displayName = info.UnitKind is CharacterUnitKind.Boss or CharacterUnitKind.AdventBattleBoss
                    ? ZString.Format("[強]{0}", info.EnemyName)
                    : info.EnemyName.ToString();

                var label = ZString.Format("{0}:{1}", info.SummonTargetId, displayName);
                debugCommandPresenter.AddButton(
                    label,
                    () =>
                    {
                        debugCommandPresenter.CloseMenu();
                        DebugSummonEnemyUnitUseCase.SummonEnemy(info.SummonTargetId);
                    });
            }
        }
    }
}
#endif

