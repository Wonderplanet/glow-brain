using System.Collections.Generic;
using System.Linq;
using Framework.Scripts.Runtime.Modules.Network.Certificates;
using GLOW.Core.Application.Configs;
using GLOW.Core.Application.Configs.APIContext;
using GLOW.Core.Application.Configs.Resolvers;
using GLOW.Core.Application.Settings.HTTPLibrary;
using GLOW.Core.Domain.Modules.Network;
using UnityHTTPLibrary;
using UnityHTTPLibrary.Certificate;
using Zenject;

namespace GLOW.Core.Application.Installers.Subs
{
    public sealed class GlowNetworkInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindInterfacesTo<ApiContextHeaderModifier>().AsCached();
            Container.BindInterfacesTo<GameApiContextHeaderModifier>().AsCached();
            Container.BindInterfacesTo<ApiContextHostBuilder>().AsCached();
            Container.BindInterfacesTo<ApiContextHeaderBuilder>().AsCached();
            Container.BindInterfacesTo<ApiContextInitializer>().AsCached();
            Container.BindInterfacesTo<ApiContextBuilder>().AsCached();

            // NOTE: アプリケーションの設定を読み込む
            Container.BindInterfacesTo<ApiHostResolver>().AsCached();
            Container.BindInterfacesTo<AuthenticatorHostResolver>().AsCached();
            Container.BindInterfacesTo<AssetCdnHostResolver>().AsCached();
            Container.BindInterfacesTo<WebCdnHostResolver>().AsCached();
            Container.BindInterfacesTo<MstCdnHostResolver>().AsCached();
            Container.BindInterfacesTo<BannerCdnHostResolver>().AsCached();
            Container.BindInterfacesTo<AnnouncementCdnHostResolver>().AsCached();
            Container.BindInterfacesTo<AgreementHostResolver>().AsCached();

            // NOTE: リクエストヘッダの設定処理をインストール
            Container.BindInterfacesTo<CommonRequestHeaderAssignor>().AsCached();
            Container.BindInterfacesTo<GameApiRequestHeaderAssignor>().AsCached();
            Container.BindInterfacesTo<AgreementRequestHeaderAssignor>().AsCached();

            // NOTE: 通信暗号化の設定処理をインストール
            Container.BindInterfacesTo<ApiEncryptSettings>().AsCached();

            BindTLSCertificateDependencies();
            Container.BindInterfacesTo<DatadogTrackedWebRequestFactoryCreator>().AsCached();
        }

        void BindTLSCertificateDependencies()
        {
            // NOTE: FQDNを用いた証明書検証処理をインストール
            Container.BindInterfacesTo<TLSFullyQualifiedDomainNameResolver>().AsCached();

            // NOTE: CertificateHandlerをインストールする
            Container.BindInterfacesTo<TLSCertificateHandler>()
                .FromMethod(context =>
                {
                    var fqdn = context.Container.Resolve<ITLSFullyQualifiedDomainNameResolver>();
                    var certificateValidators =
                        new HashSet<ITLSCertificateValidator>()
                        {
                            new TLSCertificateSubjectAlternativeNameValidator(fqdn.Resolve().ToHashSet())
                        };
                    return new TLSCertificateHandler(certificateValidators);
                })
                .AsCached();
        }
    }
}
