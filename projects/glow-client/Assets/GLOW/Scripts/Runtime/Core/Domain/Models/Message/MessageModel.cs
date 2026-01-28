using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.MessageBox;

namespace GLOW.Core.Domain.Models.Message
{
    public record MessageModel(
        MasterDataId UserMessageId,
        MasterDataId OprMessageId,
        MessageStartAtDate StartAt,
        MessageOpenedAtDate OpenedAt,
        MessageReceivedDate ReceivedAt,
        MessageExpireAt ExpireAt,
        IReadOnlyList<RewardModel> RewardModels,
        MessageTitle MessageTitle,
        MessageBody MessageBody)
    {
        public static MessageModel Empty { get; } = new MessageModel(
            MasterDataId.Empty,
            MasterDataId.Empty,
            MessageStartAtDate.Empty,
            MessageOpenedAtDate.Empty,
            MessageReceivedDate.Empty,
            MessageExpireAt.Empty,
            new List<RewardModel>(),
            MessageTitle.Empty,
            MessageBody.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
