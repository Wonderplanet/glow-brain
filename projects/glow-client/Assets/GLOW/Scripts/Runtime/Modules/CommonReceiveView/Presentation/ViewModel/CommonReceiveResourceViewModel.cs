using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Modules.CommonReceiveView.Presentation.ViewModel
{
    /// <summary>
    /// - 汎用報酬受取画面を表示するために使われるViewModel
    /// - 汎用報酬画面以外でも、報酬を表示したいときに変換される
    /// </summary>
    /// <param name="UnreceivedRewardReasonType"></param>
    /// <param name="PlayerResourceIconViewModel"></param>
    public record CommonReceiveResourceViewModel(
        UnreceivedRewardReasonType UnreceivedRewardReasonType,
        PlayerResourceIconViewModel PlayerResourceIconViewModel,
        PlayerResourceIconViewModel PreConversionPlayerResourceIconViewModel)
    {
        public static CommonReceiveResourceViewModel Empty { get; } = new(
            UnreceivedRewardReasonType.None,
            PlayerResourceIconViewModel.Empty,
            PlayerResourceIconViewModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    };
}
