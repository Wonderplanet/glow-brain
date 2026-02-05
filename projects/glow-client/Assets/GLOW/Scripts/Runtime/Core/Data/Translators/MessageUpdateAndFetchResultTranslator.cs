using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.Message;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.MessageBox;

namespace GLOW.Core.Data.Translators
{
    public class MessageUpdateAndFetchResultTranslator
    {
        public static MessageResultModel ToMessageUpdateAndFetchResultData(
            MessageResultData messageResultData)
        {
            var messageModels = messageResultData.Messages.Select(data =>
            {
                return new MessageModel(
                    new MasterDataId(data.UsrMessageId),
                    new MasterDataId(data.OprMessageId),
                    new MessageStartAtDate(data.StartAt),
                    data.OpenedAt.HasValue ? new MessageOpenedAtDate(data.OpenedAt.Value) : MessageOpenedAtDate.Empty,
                    data.ReceivedAt.HasValue
                        ? new MessageReceivedDate(data.ReceivedAt.Value)
                        : MessageReceivedDate.Empty,
                    data.ExpiredAt.HasValue
                        ? new MessageExpireAt(data.ExpiredAt.Value)
                        : MessageExpireAt.Empty,
                    data.MessageRewards.Select(r => RewardDataTranslator.Translate(r.Reward)).ToList(),
                    new MessageTitle(data.Title),
                    new MessageBody(data.Body)
                );
            }).ToList();

            return new MessageResultModel(messageModels);
        }
    }
}
