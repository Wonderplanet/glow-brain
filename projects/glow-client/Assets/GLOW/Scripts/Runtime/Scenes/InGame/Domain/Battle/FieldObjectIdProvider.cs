using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class FieldObjectIdProvider : IFieldObjectIdProvider
    {
        FieldObjectId _nextFieldObjectId = new(1);

        public FieldObjectIdProvider()
        {
        }

        public FieldObjectIdProvider(FieldObjectId initialFieldObjectId)
        {
            _nextFieldObjectId = initialFieldObjectId;
        }

        public FieldObjectId GenerateNewId()
        {
            var id = _nextFieldObjectId;
            _nextFieldObjectId = new FieldObjectId(_nextFieldObjectId.Value + 1);
            return id;
        }
    }
}
