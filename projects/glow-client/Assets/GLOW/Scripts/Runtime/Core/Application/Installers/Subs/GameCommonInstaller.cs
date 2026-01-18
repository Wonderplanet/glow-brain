using GLOW.Core.Constants.Zenject;
using GLOW.Core.Data.Repositories;
using GLOW.Core.Data.Services;
using GLOW.Core.Domain.Encoder;
using GLOW.Core.Domain.Providers;
using GLOW.Core.Domain.TimeMeasurement;
using GLOW.Scenes.InGame.Data.Repositories;
using GLOW.Scenes.ItemDetail.Application.Views;
using GLOW.Scenes.ItemDetail.Presentation.Views;
using WonderPlanet.RandomGenerator;
using WPFramework.Application.Modules;
using WPFramework.Data.Repositories;
using WPFramework.Domain.Repositories;
using Zenject;

namespace GLOW.Core.Application.Installers.Subs
{
    public sealed class GameCommonInstaller : Installer
    {
        public override void InstallBindings()
        {
            // NOTE: インゲーム関連をインストール
            Container.Bind<IAssetReferenceContainerRepository>()
                .WithId(TemplateInjectId.AssetContainer.InGame)
                .To<AssetReferenceContainerRepository>()
                .AsCached();

            // NOTE: アプリ全般で利用するServiceを読み込む
            Container.BindInterfacesTo<GameService>().AsCached();
            Container.BindInterfacesTo<StageService>().AsCached();
            Container.BindInterfacesTo<TutorialService>().AsCached();
            Container.BindInterfacesTo<AdventBattleService>().AsCached();
            Container.BindInterfacesTo<ShopService>().AsCached();
            Container.BindInterfacesTo<AgreementService>().AsCached();
            Container.BindInterfacesTo<PvpService>().AsCached();
            Container.BindInterfacesTo<EncyclopediaService>().AsCached();

            Container.BindInterfacesTo<GameRepository>().AsCached();
            Container.BindInterfacesTo<UnitSortFilterCacheRepository>().AsCached();
            Container.BindInterfacesTo<ValidatedStoreProductRepository>().AsCached();

            Container.BindViewFactoryInfo<ItemDetailViewController, ItemDetailViewControllerInstaller>();
            
            // Provider
            Container.BindInterfacesTo<DefaultStageProvider>().AsCached();

            // その他、共通のもの
            Container.BindInterfacesTo<UnityRandom>().AsCached();
            Container.BindInterfacesTo<InGameLoadingMeasurement>().AsCached();
            Container.BindInterfacesTo<SpecialAttackDescriptionEncoder>().AsCached();
        }
    }
}
