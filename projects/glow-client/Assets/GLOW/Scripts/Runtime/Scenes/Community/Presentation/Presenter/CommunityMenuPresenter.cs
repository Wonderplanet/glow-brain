using GLOW.Scenes.Community.Domain.UseCase;
using GLOW.Scenes.Community.Presentation.Translator;
using GLOW.Scenes.Community.Presentation.View;
using GLOW.Scenes.Community.Presentation.ViewModel;
using WonderPlanet.OpenURLExtension;
using Zenject;

namespace GLOW.Scenes.Community.Presentation.Presenter
{
    /// <summary>
    /// 121_メニュー
    /// 　121-1メニュー（ホーム画面）
    /// 　121-5_メディア
    /// 　　121-5-1_メディア
    /// </summary>
    public class CommunityMenuPresenter : ICommunityMenuViewDelegate
    {
        [Inject] CommunityMenuViewController ViewController { get; }
        [Inject] CommunityListUseCase CommunityListUseCase { get; }
        void ICommunityMenuViewDelegate.OnViewDidLoad()
        {
            var model = CommunityListUseCase.GetCommunityList();
            var viewModel = CommunityMenuViewModelTranslator.ToCommunityMenuViewModel(model);
            ViewController.SetCommunityMenuListComponents(viewModel);
        }

        void ICommunityMenuViewDelegate.OnCloseSelected()
        {
            ViewController.Dismiss();
        }

        public void OnCommunityBannerSelected(CommunityMenuCellViewModel viewModel)
        {
            var info = new UrlWithCustomUrlSchemaInfo(viewModel.Url.Value, viewModel.Schema.Value);
            CustomOpenURL.Open(info);
        }
    }
}
