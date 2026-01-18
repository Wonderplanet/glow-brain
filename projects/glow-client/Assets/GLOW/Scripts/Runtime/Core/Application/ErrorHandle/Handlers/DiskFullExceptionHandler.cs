using System;
using WPFramework.Application.ErrorHandle;
using WPFramework.Exceptions;
using WPFramework.Modules.Localization.Terms;
using Zenject;

namespace GLOW.Core.Application.ErrorHandle.Handlers
{
    public sealed class DiskFullExceptionHandler : IDiskFullExceptionHandler
    {
        [Inject] ILocalizationTermsSource Terms { get; }
        [Inject] ICommonExceptionViewer CommonExceptionViewer { get; }

        bool IDiskFullExceptionHandler.Handle(DiskFullException exception, Action completion)
        {
            CommonExceptionViewer.Show(
                Terms.Get("disk_full_error_dialog_title"),
                Terms.Get("disk_full_error_dialog_message"),
                exception,
                completion);

            return true;
        }
    }
}
