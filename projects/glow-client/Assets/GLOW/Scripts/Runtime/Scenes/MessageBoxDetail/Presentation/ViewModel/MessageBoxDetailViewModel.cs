using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.MessageBox;
using GLOW.Core.Presentation.ViewModels;

namespace GLOW.Scenes.MessageBoxDetail.Presentation.ViewModel
{
    public record MessageBoxDetailViewModel(
        MasterDataId MessageId,
        MessageFormatType MessageFormatType,
        MessageStatus MessageStatus,
        MessageTitle MessageTitle,
        MessageBody MessageBody,
        IReadOnlyList<PlayerResourceIconViewModel> RewardList,
        MessageStartAtDate MessageStartAtDate,
        RemainingTimeSpan LimitTime
    );
}
