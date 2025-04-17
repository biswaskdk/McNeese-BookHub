class Node:
	def __init__(self, value, next=None):
		self.value = value
		self.next = next

# For testing
def print_linked_list(head):
    current = head
    while current:
        print(current.value, end=" -> " if current.next else "\n")
        current = current.next

def count_critical_points(song_audio):
    if not song_audio or not song_audio.next or not song_audio.next.next:
        return 0
    
    count = 0
    prev = song_audio
    current = song_audio.next
    next_node = current.next 
    
    while next_node:
        if (current.value < prev.value and current.value < next_node.value) or \
        (current.value > prev.value and current.value > next_node.value):
            count += 1 
        prev = current
        current = next_node
        next_node = next_node.next
        
    return count 


song_audio = Node(5, Node(3, Node(1, Node(2, Node(5, Node(1, Node(2)))))))

print(count_critical_points(song_audio))