using System;
using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Scenes.Home.Domain.Models;
using GLOW.Scenes.Home.Presentation.ViewModels;
using GLOW.Scenes.Home.Presentation.Views;
using Zenject;

namespace GLOW.Scenes.Home.Presentation.Presenters.HomeAppearanceAction
{
    /// <summary>
    /// ホーム画面ログイン時のダイアログ表示・演出を統合管理するクラス
    /// </summary>
    public class HomeAppearanceActionExecutor : IHomeAppearanceActionExecutor
    {
        [Inject] HomeMainViewController HomeMainViewController { get; }

        IReadOnlyList<IHomeAppearanceAction> _actions = new List<IHomeAppearanceAction> ();

        public void SetActions(IReadOnlyList<IHomeAppearanceAction> actions)
        {
            _actions = actions;
        }

        async UniTask IHomeAppearanceActionExecutor.ExecuteActions(
            CancellationToken cancellationToken,
            HomeMainQuestViewModel homeMainViewModel,
            DisplayAtLoginUseCaseModel displayAtLoginModel,
            Action onCloseCompletion)
        {
            try
            {
                await ExecuteActionsInternal(cancellationToken, homeMainViewModel, displayAtLoginModel, onCloseCompletion);
            }
            finally
            {
                HomeMainViewController.UserInteraction(true);
            }
        }

        async UniTask ExecuteActionsInternal(
            CancellationToken cancellationToken,
            HomeMainQuestViewModel homeMainViewModel,
            DisplayAtLoginUseCaseModel displayAtLoginModel,
            Action onCloseCompletion)
        {
            var context = new HomeAppearanceActionContext(homeMainViewModel, displayAtLoginModel);

            foreach (var action in _actions)
            {
                if (cancellationToken.IsCancellationRequested)
                {
                    throw new OperationCanceledException(cancellationToken);
                }

                HomeMainViewController.UserInteraction(false);

                await action.ExecuteAsync(context, onCloseCompletion, cancellationToken);
            }
        }
    }
}
