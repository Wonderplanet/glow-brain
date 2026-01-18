using GLOW.Scenes.EmblemDetail.Domain.UseCases;
using GLOW.Scenes.EmblemDetail.Presentation.Translators;
using GLOW.Scenes.EmblemDetail.Presentation.ViewModels;
using GLOW.Scenes.EmblemDetail.Presentation.Views;
using Zenject;

namespace GLOW.Scenes.EmblemDetail.Presentation.Presenters
{
    /// <summary>
    /// エンブレム詳細ダイアログ
    /// </summary>
    public class EmblemDetailPresenter : IEmblemDetailViewDelegate
    {
        [Inject] EmblemDetailViewController ViewController { get; }
        [Inject] EmblemDetailViewController.Argument Argument { get; }
        [Inject] GetEmblemDetailUseCase GetEmblemDetailUseCase { get; }

        public void OnViewDidLoad()
        {
            var model = GetEmblemDetailUseCase.GetEmblemDetail(Argument.MstEmblemId);
            var viewModel = EmblemDetailViewModelTranslator.Translate(model);

            ViewController.SetUp(viewModel);
        }

        public void OnCloseButtonTapped()
        {
            ViewController.Dismiss();
        }
    }
}
