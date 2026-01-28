using System;
using System.Text;
using UnityEngine.AddressableAssets;
using WPFramework.Application.ErrorHandle;
using WPFramework.Modules.Localization.Terms;
using Zenject;

namespace GLOW.Core.Application.ErrorHandle.Handlers
{
    public class AddressablesInvalidKeyExceptionHandler : IAddressablesInvalidKeyExceptionHandler
    {
        [Inject] ILocalizationTermsSource Terms { get; }
        [Inject] ICommonExceptionViewer CommonExceptionViewer { get; }

        public bool Handle(InvalidKeyException exception, Action completion)
        {
            // NOTE: デバッグ時にはどのファイルが読み込めなかったかを表示する
            var message = Terms.Get("asset_load_error_dialog_message");
#if GLOW_DEBUG
            var builder = new StringBuilder();
            builder.AppendLine(message);
            builder.AppendLine($"Asset Key={exception.Key}");
            builder.AppendLine($"Asset Type={exception.Type}");
            message = builder.ToString();
#endif  // DEBUG

            CommonExceptionViewer.Show(
                Terms.Get("asset_load_error_dialog_title"),
                message,
                exception,
                completion);

            return true;
        }
    }
}
