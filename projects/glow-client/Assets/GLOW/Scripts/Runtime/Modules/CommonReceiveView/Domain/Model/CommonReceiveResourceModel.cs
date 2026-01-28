using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;

namespace GLOW.Modules.CommonReceiveView.Domain.Model
{
    /// <summary>
    /// - 主に汎用報酬受取画面を表示するために使われるEntity
    /// - サーバーから報酬や情報を受け取ったときに変換される
    /// - コード内では表示にのみ使われるケースも存在している
    /// </summary>
    /// <param name="UnreceivedRewardReasonType"></param>
    /// <param name="PlayerResourceModel"></param>
    public record CommonReceiveResourceModel(
        UnreceivedRewardReasonType UnreceivedRewardReasonType,
        PlayerResourceModel PlayerResourceModel,
        PlayerResourceModel PreConversionPlayerResourceModel)
    {
        public static CommonReceiveResourceModel Empty { get; } =
            new(UnreceivedRewardReasonType.None,
                PlayerResourceModel.Empty,
                PlayerResourceModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    };
}
