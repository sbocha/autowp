<?php
class Factory_Row extends Project_Db_Table_Row
{
    public function getRelatedCarGroupId()
    {
        $db = $this->getTable()->getAdapter();

        $carIds = $db->fetchCol(
            $db->select()
                ->from('factory_car', 'car_id')
                ->where('factory_car.factory_id = ?', $this->id)
        );

        $vectors = array();
        foreach ($carIds as $carId) {
            $parentIds = $db->fetchCol(
                $db->select()
                    ->from('car_parent_cache', 'parent_id')
                    ->where('car_id = ?', $carId)
                    ->where('car_id <> parent_id')
                    ->order('diff desc')
            );

            // remove parents
            foreach ($parentIds as $parentId) {
                $index = array_search($parentId, $carIds);
                if ($index !== false) {
                    unset($carIds[$index]);
                }
            }

            $vector = $parentIds;
            $vector[] = $carId;

            $vectors[] = $vector;
        }

        do {
            // look for same root

            $matched = false;
            for ($i=0; ($i<count($vectors)-1) && !$matched; $i++) {
                for ($j=$i+1; $j<count($vectors) && !$matched; $j++) {
                    if ($vectors[$i][0] == $vectors[$j][0]) {
                        $matched = true;
                        // matched root
                        $newVector = array();
                        $length = min(count($vectors[$i]), count($vectors[$j]));
                        for ($k=0; $k<$length && $vectors[$i][$k] == $vectors[$j][$k]; $k++) {
                            $newVector[] = $vectors[$i][$k];
                        }
                        $vectors[$i] = $newVector;
                        array_splice($vectors, $j, 1);
                    }
                }
            }

        } while($matched);

        $resultIds = array();
        foreach ($vectors as $vector) {
            $resultIds[] = $vector[count($vector)-1];
        }

        return $resultIds;
    }

    public function getRelatedCarGroups()
    {
        $db = $this->getTable()->getAdapter();

        $carIds = $db->fetchCol(
            $db->select()
                ->from('factory_car', 'car_id')
                ->where('factory_car.factory_id = ?', $this->id)
        );

        $vectors = array();
        foreach ($carIds as $carId) {
            $parentIds = $db->fetchCol(
                $db->select()
                    ->from('car_parent_cache', 'parent_id')
                    ->where('car_id = ?', $carId)
                    ->where('car_id <> parent_id')
                    ->order('diff desc')
            );

            // remove parents
            foreach ($parentIds as $parentId) {
                $index = array_search($parentId, $carIds);
                if ($index !== false) {
                    unset($carIds[$index]);
                }
            }

            $vector = $parentIds;
            $vector[] = $carId;

            $vectors[] = array(
                'parents' => $vector,
                'childs'  => array($carId)
            );
        }

        do {
            // look for same root

            $matched = false;
            for ($i=0; ($i<count($vectors)-1) && !$matched; $i++) {
                for ($j=$i+1; $j<count($vectors) && !$matched; $j++) {
                    if ($vectors[$i]['parents'][0] == $vectors[$j]['parents'][0]) {
                        $matched = true;
                        // matched root
                        $newVector = array();
                        $length = min(count($vectors[$i]['parents']), count($vectors[$j]['parents']));
                        for ($k=0; $k<$length && $vectors[$i]['parents'][$k] == $vectors[$j]['parents'][$k]; $k++) {
                            $newVector[] = $vectors[$i]['parents'][$k];
                        }
                        $vectors[$i] = array(
                            'parents' => $newVector,
                            'childs'  => array_merge($vectors[$i]['childs'], $vectors[$j]['childs'])
                        );
                        array_splice($vectors, $j, 1);
                    }
                }
            }

        } while($matched);

        $result = array();
        foreach ($vectors as $vector) {
            $carId = $vector['parents'][count($vector['parents'])-1];
            $result[$carId] = $vector['childs'];
        }

        return $result;
    }
}