using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Constants;
using GLOW.Core.Presentation.Modules;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.ArtworkEnhance.Domain.UseCase;
using GLOW.Scenes.ArtworkEnhance.Domain.ValueObjects;
using GLOW.Scenes.ArtworkEnhance.Presentation.Translator;
using GLOW.Scenes.ArtworkEnhance.Presentation.View;
using GLOW.Scenes.ArtworkEnhance.Presentation.ViewModels;
using GLOW.Scenes.ItemDetail.Presentation.Presenters;
using Zenject;

namespace GLOW.Scenes.ArtworkEnhance.Presentation.Presenter
{
    public class ArtworkGradeContentsPresenter : IArtworkGradeContentsDelegate
    {
        [Inject] ArtworkGradeContentsViewController.Argument Argument { get; }
        [Inject] ArtworkGradeContentsViewController ViewController { get; }
        [Inject] IItemDetailWireFrame ItemDetailWireFrame { get; }
        [Inject] ArtworkGradeContentsUseCase ArtworkGradeContentsUseCase { get; }

        void IArtworkGradeContentsDelegate.OnViewDidLoad()
        {
            var useCaseModel = ArtworkGradeContentsUseCase.GetArtworkRankUpContentsUseCaseModel(Argument.MstArtworkId);
            var viewModel = ArtworkGradeContentsViewModelTranslator.Translate(useCaseModel);
            ViewController.Setup(viewModel, model =>
            {
                ItemDetailUtil.Main.ShowItemDetailView(model, ViewController);
            });
        }

        void IArtworkGradeContentsDelegate.OnItemIconTapped(PlayerResourceIconViewModel iconViewModel)
        {
            ItemDetailWireFrame.ShowItemDetailView(iconViewModel, ViewController);
        }

        void IArtworkGradeContentsDelegate.OnCloseButtonTapped()
        {
            ViewController.CloseView();
        }
    }
}
