using System.Collections.Generic;

namespace GLOW.Scenes.MessageBox.Domain.Model
{
    public record MessageCommonResultUseCaseModel(
        IReadOnlyList<MessageListUseCaseModel> UpdatedList,
        bool CanBulkReceive,
        bool CanBulkOpen)
    {
        public static MessageCommonResultUseCaseModel Empty { get; } = new(
            new List<MessageListUseCaseModel>(),
            false,
            false);
        
        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}