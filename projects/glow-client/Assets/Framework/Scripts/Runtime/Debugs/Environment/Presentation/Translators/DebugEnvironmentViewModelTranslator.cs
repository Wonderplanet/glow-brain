using WPFramework.Debugs.Environment.Presentation.ViewModels;
using WPFramework.Domain.Models;

namespace WPFramework.Debugs.Environment.Presentation.Translators
{
    public class DebugEnvironmentViewModelTranslator : IDebugEnvironmentViewModelTranslator
    {
        DebugEnvironmentSpecifiedDomainViewModel IDebugEnvironmentViewModelTranslator.TranslateToViewModel(EnvironmentModel model)
        {
            var viewModel = new DebugEnvironmentSpecifiedDomainViewModel(
                api: model.Api,
                assetCdn: model.AssetCdn,
                masterCdn: model.MasterCdn,
                webCdn: model.WebCdn,
                announcementCdn: model.AnnouncementCdn,
                bannerCdn: model.BannerCdn,
                agreementCdn: model.AgreementCdn);
            return viewModel;
        }

        EnvironmentModel IDebugEnvironmentViewModelTranslator.TranslateToModel(string name, string env, string description, DebugEnvironmentSpecifiedDomainViewModel viewModel)
        {
            var model = new EnvironmentModel(
                Name: name,
                Env: env,
                Api: viewModel.Api,
                Description: description,
                AssetCdn: viewModel.AssetCdn,
                MasterCdn: viewModel.MasterCdn,
                WebCdn: viewModel.WebCdn,
                AnnouncementCdn: viewModel.AnnouncementCdn,
                BannerCdn: viewModel.BannerCdn,
                AgreementCdn: viewModel.AgreementCdn);
            return model;
        }
    }
}
