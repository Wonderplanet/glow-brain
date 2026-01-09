using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.MessageBox;
using GLOW.Scenes.MessageBox.Domain.ValueObject;

namespace GLOW.Scenes.MessageBox.Domain.Model
{
    public record MessageListUseCaseModel(
        MasterDataId MessageId,
        MasterDataId OprId,
        MessageFormatType MessageFormatType,
        MessageStatus MessageStatus,
        MessageActionCompletedFlag IsActionCompleted,
        IReadOnlyList<PlayerResourceModel> MessageRewards,
        MessageTitle MessageTitle,
        MessageBody MessageBody,
        MessageStartAtDate MessageStartAtDate,
        MessageExpireAt MessageExpireAt,
        RemainingTimeSpan LimitTime)
    {
        public static MessageListUseCaseModel Empty { get; } = new MessageListUseCaseModel(
            MasterDataId.Empty,
            MasterDataId.Empty,
            MessageFormatType.HasNotReward,
            MessageStatus.New,
            MessageActionCompletedFlag.False,
            new List<PlayerResourceModel>(),
            MessageTitle.Empty,
            MessageBody.Empty,
            MessageStartAtDate.Empty,
            MessageExpireAt.Empty,
            RemainingTimeSpan.Empty);

        public bool IsExpired(DateTimeOffset nowTime)
        {
            // 期限が無期限ではない、かつ現在時間の方が後の場合は期限切れ
            return !MessageExpireAt.IsEmpty() && MessageExpireAt.Value < nowTime;
        }
    }
}