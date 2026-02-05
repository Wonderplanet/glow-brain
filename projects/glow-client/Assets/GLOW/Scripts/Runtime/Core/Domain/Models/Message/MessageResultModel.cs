using System.Collections.Generic;

namespace GLOW.Core.Domain.Models.Message
{
    public record MessageResultModel(IReadOnlyList<MessageModel> Messages)
    {
        public static MessageResultModel Empty { get; } = new MessageResultModel(new List<MessageModel>());

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }

}