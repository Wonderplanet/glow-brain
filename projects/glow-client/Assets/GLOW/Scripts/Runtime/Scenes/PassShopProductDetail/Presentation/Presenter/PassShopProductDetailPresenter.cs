using GLOW.Scenes.PassShopProductDetail.Domain.UseCase;
using GLOW.Scenes.PassShopProductDetail.Presentation.Translator;
using GLOW.Scenes.PassShopProductDetail.Presentation.View;
using Zenject;

namespace GLOW.Scenes.PassShopProductDetail.Presentation.Presenter
{
    public class PassShopProductDetailPresenter : IPassShopProductDetailViewDelegate
    {
        [Inject] PassShopProductDetailViewController ViewController { get; }
        [Inject] PassShopProductDetailViewController.Argument Argument { get; }
        [Inject] ShowPassShopProductDetailUseCase ShowPassShopProductDetailUseCase { get; }
        
        void IPassShopProductDetailViewDelegate.OnViewDidLoad()
        {
            var model = ShowPassShopProductDetailUseCase.GetPassShopProductDetail(Argument.MstShopPassId);
            var viewModel = PassShopProductDetailViewModelTranslator.ToPassShopProductDetailViewModel(model);
            
            ViewController.SetUpViewUi(viewModel);
        }

        void IPassShopProductDetailViewDelegate.OnCloseSelected()
        {
            ViewController.Dismiss();
        }
    }
}