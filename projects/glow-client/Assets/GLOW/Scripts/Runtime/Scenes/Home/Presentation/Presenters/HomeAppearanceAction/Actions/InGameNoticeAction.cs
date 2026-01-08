using System;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects.Notice;
using GLOW.Scenes.Notice.Presentation.Facade;
using GLOW.Scenes.Notice.Presentation.Translator;
using Zenject;

namespace GLOW.Scenes.Home.Presentation.Presenters.HomeAppearanceAction
{
    /// <summary>
    /// IGN表示
    /// </summary>
    public class InGameNoticeAction : IHomeAppearanceAction
    {
        public class Factory : PlaceholderFactory<InGameNoticeAction> { }

        [Inject] INoticeViewFacade NoticeViewFacade { get; }

        public async UniTask ExecuteAsync(HomeAppearanceActionContext context, Action onCloseCompletion,
            CancellationToken cancellationToken)
        {
            if (!context.DisplayAtLoginModel.ShowNotices.Any()) return;

            var viewModels = context.DisplayAtLoginModel.ShowNotices
                .Select(NoticeViewModelTranslator.ToInGameNoticeViewModel)
                .ToList();

            await UniTask.Delay(TimeSpan.FromSeconds(0.1f), cancellationToken: cancellationToken);

            NoticeTransitionFlag isNoticeTransition = await NoticeViewFacade.ShowInGameNotice(viewModels, cancellationToken);
            // 遷移ボタンがタップされた場合は以降をキャンセルする
            if (isNoticeTransition)
            {
                throw new OperationCanceledException(cancellationToken);
            }
        }
    }
}
