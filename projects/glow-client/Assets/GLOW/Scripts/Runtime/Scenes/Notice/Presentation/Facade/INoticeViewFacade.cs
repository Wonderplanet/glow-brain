using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects.Notice;
using GLOW.Scenes.Notice.Presentation.ViewModel;

namespace GLOW.Scenes.Notice.Presentation.Facade
{
    public interface INoticeViewFacade
    {
        UniTask<NoticeTransitionFlag> ShowInGameNotice(
            IReadOnlyList<NoticeViewModel> viewModels,
            CancellationToken cancellationToken);

        void ShowInGameNoticeWithBannerDownload(NoticeViewModel viewModel);
    }
}