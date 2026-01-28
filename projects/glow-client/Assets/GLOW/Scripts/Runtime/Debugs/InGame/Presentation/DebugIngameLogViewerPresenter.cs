#if GLOW_INGAME_DEBUG
using System;
using Cysharp.Threading.Tasks;
using Cysharp.Threading.Tasks.Linq;
using GLOW.Debugs.InGame.Domain.Models;
using GLOW.Debugs.InGame.Domain.UseCases;
using GLOW.Debugs.InGame.Presentation.DebugIngameLogView;
using WonderPlanet.UniTaskSupporter;
using Zenject;

namespace GLOW.Debugs.InGame.Presentation
{
    public sealed class DebugIngameLogViewerPresenter : IDebugIngameLogViewerViewDelegate
    {
        [Inject] DebugIngameLogViewerViewController ViewController { get; }
        [Inject] DebugIngameLogViewerUseCase UseCase { get; }

        void IDebugIngameLogViewerViewDelegate.Init(Action<DebugInGameLogDamageModel> onDamageReport)
        {
            DoAsync.Invoke(ViewController.ActualView, async cancellationToken =>
            {
                // NOTE: 毎ループ確認する
                await foreach (var _ in UniTaskAsyncEnumerable.EveryUpdate())
                {
                    UpdateLogStatus();

                    // NOTE: 0.1秒間待機（FPSレベルで更新しない）
                    await UniTask.Delay(TimeSpan.FromSeconds(0.1f), cancellationToken: cancellationToken);
                }
            });
            UseCase.SubscribeDamageReport(onDamageReport);
        }

        void IDebugIngameLogViewerViewDelegate.ViewDidDisappear()
        {
            UseCase.UnSubscribeDamageReport();
        }


        void UpdateLogStatus()
        {
            ViewController.UpdateTickCount(UseCase.GetCurrentTickCount());

            var currentType = ViewController.CurrentDebugUnitStatusType;
            if (currentType == DebugUnitStatusType.PlayerField || currentType == DebugUnitStatusType.EnemyField)
            {
                ViewController.UpdateUnitStatus(UseCase.GetUnitModels(), UseCase.GetEnemyModels());
            }
            else
            {
                ViewController.UpdateDeckStatus(UseCase.GetPlayerDeckModels(), UseCase.GetEnemyDeckModels());
            }
        }
    }
}
#endif //GLOW_INGAME_DEBUG
