using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Scenes.Home.Presentation.Views;
using Zenject;

namespace GLOW.Scenes.Home.Presentation.Presenters.HomeAppearanceAction
{
    /// <summary>
    /// クエスト・ステージ初期化とクエスト開放演出
    /// </summary>
    public class QuestInitializationAction : IHomeAppearanceAction
    {
        public class Factory : PlaceholderFactory<QuestInitializationAction> { }

        [Inject] HomeMainViewController HomeMainViewController { get; }

        public async UniTask ExecuteAsync(HomeAppearanceActionContext context, Action onCloseCompletion,
            CancellationToken cancellationToken)
        {
            if (context.HomeMainViewModel.IsEmpty()) return;

            // NOTE: クエスト・ステージ初期化、クエスト開放演出
            await HomeMainViewController.InitQuestViewModel(context.HomeMainViewModel, cancellationToken);
        }
    }
}
